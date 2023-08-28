<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Attribute\Data;
use PhpAccessor\File\File;
use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

use function in_array;

use const DIRECTORY_SEPARATOR;

class ClassProcessor extends NodeVisitorAbstract
{
    private const PRIMITIVE_TYPES = [
        'null' => 'null',
        'bool' => 'bool',
        'boolean' => 'bool',
        'string' => 'string',
        'int' => 'int',
        'integer' => 'int',
        'float' => 'float',
        'double' => 'float',
        'array' => 'array',
        'object' => 'object',
        'callable' => 'callable',
        'resource' => 'resource',
        'mixed' => 'mixed',
        'iterable' => 'iterable',
    ];

    /** @var AccessorMethodInterface[] */
    private array $accessorMethods = [];

    private string $classname = '';

    private bool $genCompleted = false;

    private TraitAccessor $traitAccessor;

    private NodeFinder $nodeFinder;

    private Lexer $phpDocLexer;

    private PhpDocParser $phpDocParser;

    /** @var Property[] */
    private array $originalProperties = [];

    /** @var string[] */
    private array $originalMethods = [];

    public function __construct(
        private bool $genMethod,
        private NameContext $nameContext,
    ) {
        $this->nodeFinder = new NodeFinder();
        $this->phpDocLexer = new Lexer();
        $constantExpressionParser = new ConstExprParser();
        $this->phpDocParser = new PhpDocParser(new TypeParser($constantExpressionParser), $constantExpressionParser);
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof Class_ || empty($node->attrGroups)) {
            return null;
        }

        $this->classname = '\\' . $node->namespacedName->toString();
        $attributeProcessor = new AttributeProcessor($node);
        if (! $attributeProcessor->isPending()) {
            return null;
        }

        if (! $this->parsePropertiesAndMethods($node)) {
            return null;
        }

        $this->generateAllMethods($node->namespacedName->toString(), $attributeProcessor);
        $this->buildTraitAccessor();
        if (empty($this->accessorMethods) || ! $this->genMethod) {
            return null;
        }

        $this->genCompleted = true;

        return $this->rebuildClass($node);
    }

    public function afterTraverse(array $nodes)
    {
        if (! $this->genCompleted) {
            return null;
        }
        // TODO:待优化
        $nodeFinder = new NodeFinder();
        /** @var Namespace_ $namespace */
        $namespace = $nodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        $include = new Expression(new Include_(new String_(File::ACCESSOR . DIRECTORY_SEPARATOR . $this->traitAccessor->getClassName() . '.php'), Include_::TYPE_INCLUDE_ONCE));
        array_unshift($namespace->stmts, $include);

        return $nodes;
    }

    public function isGenCompleted(): bool
    {
        return $this->genCompleted;
    }

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function getAccessorMethods(): array
    {
        return $this->accessorMethods;
    }

    public function getTraitAccessor(): TraitAccessor
    {
        return $this->traitAccessor;
    }

    protected function buildDocComment(Node $node): ?PhpDocNode
    {
        if (empty($docComment = $node->getDocComment())) {
            return null;
        }

        $tokens = new TokenIterator($this->phpDocLexer->tokenize($docComment->getText()));
        $ast = $this->phpDocParser->parse($tokens);
        foreach ($ast->getVarTagValues() as $varTagValueNode) {
            $this->resolveTypeNode($varTagValueNode->type);
        }
        $node->setDocComment(new Doc((string) $ast));

        return $ast;
    }

    protected function resolveTypeNode(TypeNode $typeNode): void
    {
        if ($typeNode instanceof IdentifierTypeNode) {
            $typeNode->name = $this->resolveTypeName($typeNode);

            return;
        }

        if ($typeNode instanceof ArrayTypeNode) {
            $this->resolveTypeNode($typeNode->type);
        } elseif ($typeNode instanceof GenericTypeNode) {
            foreach ($typeNode->genericTypes as $genericType) {
                $this->resolveTypeNode($genericType);
            }
        } elseif ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $type) {
                $this->resolveTypeNode($type);
            }
        } elseif ($typeNode instanceof ArrayShapeNode) {
            foreach ($typeNode->items as $item) {
                $this->resolveTypeNode($item->valueType);
            }
        }
    }

    protected function resolveTypeName(IdentifierTypeNode $node): string
    {
        if (isset(self::PRIMITIVE_TYPES[$node->name])) {
            return $node->name;
        }

        $resolvedName = $this->nameContext->getResolvedName(new Node\Name($node->name), Node\Stmt\Use_::TYPE_NORMAL);
        if (empty($resolvedName)) {
            return $node->name;
        }

        return $resolvedName->toCodeString();
    }

    private function parsePropertiesAndMethods(Node $node): bool
    {
        $this->originalProperties = $this->nodeFinder->findInstanceOf($node, Property::class);
        /** @var ClassMethod[] $originalClassMethods */
        $originalClassMethods = $this->nodeFinder->findInstanceOf($node, ClassMethod::class);
        foreach ($originalClassMethods as $method) {
            $this->originalMethods[] = $method->name->name;
            if (empty($method->getParams()) || $method->name->name != '__construct') {
                continue;
            }

            foreach ($method->getParams() as $param) {
                if ($param->flags == 0) {
                    continue;
                }

                $propertyBuilder = new \PhpParser\Builder\Property($param->var->name);
                $propertyBuilder->setDefault($param->default);
                $property = $propertyBuilder->getNode();
                $property->flags = $param->flags;
                $property->type = $param->type;
                $this->originalProperties[] = $property;
            }
        }

        return ! empty($this->originalProperties);
    }

    private function generateAllMethods(
        string $classname,
        AttributeProcessor $attributeProcessor,
    ): void {
        foreach ($this->originalProperties as $property) {
            if ($attributeProcessor->ignoreProperty($property)) {
                continue;
            }

            $docComment = $this->buildDocComment($property);
            foreach ($property->props as $prop) {
                $this->accessorMethods = array_merge(
                    $this->accessorMethods,
                    MethodFactory::createFromField($classname, $prop, $property->type, $docComment, $attributeProcessor)
                );
            }
        }
    }

    private function buildTraitAccessor(): void
    {
        $this->traitAccessor = new TraitAccessor($this->classname);
        foreach ($this->accessorMethods as $accessorMethod) {
            if (in_array($accessorMethod->getMethodName(), $this->originalMethods)) {
                continue;
            }

            $this->traitAccessor->addAccessorMethod($accessorMethod);
        }
    }

    private function rebuildClass(Class_ $node): Class_
    {
        $builder = new BuilderFactory();
        $class = $builder
            ->class($node->name->toString())
            ->addStmt($builder->useTrait('\\' . $this->traitAccessor->getClassName()));
        $node->extends && $class->extend($node->extends);
        $node->isAbstract() && $class->makeAbstract();

        foreach ($node->attrGroups as $attrGroup) {
            $ignore = false;
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() == Data::class) {
                    $ignore = true;
                    break;
                }
            }
            if ($ignore) {
                continue;
            }

            $class->addAttribute($attrGroup);
        }

        foreach ($node->implements as $implement) {
            $class->implement($implement);
        }

        $newNode = $class->getNode();
        $newNode->stmts = array_merge($newNode->stmts, $node->stmts);

        return $newNode;
    }
}
