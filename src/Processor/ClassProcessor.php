<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor;

use PhpAccessor\File\File;
use PhpAccessor\Processor\Builder\DataBuilder;
use PhpAccessor\Processor\Method\AccessorMethod;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

use const DIRECTORY_SEPARATOR;

class ClassProcessor extends NodeVisitorAbstract
{
    /** @var AccessorMethod[] */
    private array $accessorMethods = [];

    private string $classname = '';

    private string $namespace = '';

    /**
     * @var array<string, string>
     */
    private array $usedClassNames = [];

    private const PRIMITIVE_TYPES = [
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

    private bool $genCompleted = false;

    private TraitAccessor $traitAccessor;

    private NodeFinder $nodeFinder;

    public function __construct(
        private bool $genMethod
    ) {
        $this->nodeFinder = new NodeFinder();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Use_) {
            $this->collectUsedClassNames($node);

            return null;
        }
        if (!$node instanceof Class_ || empty($node->attrGroups)) {
            return null;
        }

        $this->classname = '\\' . $node->namespacedName->toString();
        $this->namespace = str_replace("\\{$node->name->toString()}", '', $this->classname);
        $attributeProcessor = new AttributeProcessor();

        /** @var Attribute[] $attributes */
        $attributes = $this->nodeFinder->findInstanceOf($node->attrGroups, Attribute::class);
        foreach ($attributes as $attribute) {
            $dataBuilder = new DataBuilder();
            $attributeProcessor->setData($dataBuilder->setAttribute($attribute)->build());
        }

        if (!$attributeProcessor->isPending()) {
            return null;
        }

        /** @var Property[] $properties */
        $properties = $this->nodeFinder->findInstanceOf($node, Property::class);
        if (empty($properties)) {
            return null;
        }

        $this->processPropertiesDocComment($properties);

        $this->generateAllMethods($node->namespacedName->toString(), $properties, $attributeProcessor);
        /** @var ClassMethod[] $originalClassMethods */
        $originalClassMethods = $this->nodeFinder->findInstanceOf($node, ClassMethod::class);
        $originalClassMethodNames = [];
        foreach ($originalClassMethods as $originalClassMethod) {
            $originalClassMethodNames[] = $originalClassMethod->name->toString();
        }
        $this->buildTraitAccessor($originalClassMethodNames);
        if (empty($this->accessorMethods) || !$this->genMethod) {
            return null;
        }

        $this->genCompleted = true;

        return $this->rebuildClass($node);
    }

    public function afterTraverse(array $nodes)
    {
        if (!$this->genCompleted) {
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

    /**
     * @param Property[] $properties
     */
    private function generateAllMethods(
        string $classname,
        array $properties,
        AttributeProcessor $attributeProcessor,
    ): void {
        foreach ($properties as $property) {
            /** @var Attribute[] $attributes */
            $attributes = $this->nodeFinder->findInstanceOf($property->attrGroups, Attribute::class);
            $attributeProcessor->buildPropertyAttributes($property, $attributes);
        }
        foreach ($attributeProcessor->getPendingProperties()  as $pendingProperty) {
            $this->accessorMethods = array_merge(
                $this->accessorMethods,
                MethodFactory::createFromField($classname, $pendingProperty['prop'], $pendingProperty['type'], $pendingProperty['doc'], $attributeProcessor)
            );
        }
    }

    private function buildTraitAccessor($originalClassMethodNames): void
    {
        $this->traitAccessor = new TraitAccessor($this->classname);
        foreach ($this->accessorMethods as $accessorMethod) {
            if (\in_array($accessorMethod->getMethodName(), $originalClassMethodNames)) {
                continue;
            }
            $this->traitAccessor->addAccessorMethod($accessorMethod);
        }
    }

    private function rebuildClass(Class_ $node): Class_
    {
        $builder = new BuilderFactory();
        $class = $builder
            ->class($node->name)
            ->addStmt($builder->useTrait('\\' . $this->traitAccessor->getClassName()));
        $node->extends && $class->extend($node->extends);
        foreach ($node->implements as $implement) {
            $class->implement($implement);
        }
        $newNode = $class->getNode();
        $newNode->stmts = array_merge($newNode->stmts, $node->stmts);

        return $newNode;
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

    private function collectUsedClassNames(Use_ $node): void
    {
        foreach ($node->uses as $use) {
            $aliasName = $use->name->getLast();
            if (!empty($use->alias)) {
                $aliasName = $use->alias->toString();
            }
            $this->usedClassNames[strtolower($aliasName)] = $use->name->toString();
        }
    }

    /**
     * @param Property[] $properties
     */
    private function processPropertiesDocComment(array $properties): void
    {
        foreach ($properties as $property) {
            if (empty($docComment = $property->getDocComment())) {
                continue;
            }

            if (!preg_match('/(?<=@var\s)[^\s]+/', $docComment->getText(), $matches)) {
                continue;
            }

            $type = $matches[0];
            $isArray = str_ends_with($type, '[]');
            if ($isArray) {
                $type = substr($type, 0, -2);
            }

            if (isset(self::PRIMITIVE_TYPES[$type])) {
                continue;
            }

            $type = $this->getUsedClassName($type) . ($isArray ? '[]' : '');
            $text = str_replace($matches[0], $type, $docComment->getText());

            $property->setDocComment(new Doc($text));
        }
    }

    private function getUsedClassName(string $type): ?string
    {
        $alias = ($pos = strpos($type, '\\')) === false ? $type : substr($type, 0, $pos);
        $loweredAlias = strtolower($alias);

        if (isset($this->usedClassNames[$loweredAlias])) {
            if (false !== $pos) {
                return $this->usedClassNames[$loweredAlias] . substr($type, $pos);
            }

            return $this->usedClassNames[$loweredAlias];
        }

        return "{$this->namespace}\\{$type}";
    }
}
