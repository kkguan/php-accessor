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
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

use function in_array;

use const DIRECTORY_SEPARATOR;

class ClassProcessor extends NodeVisitorAbstract
{
    /** @var AccessorMethodInterface[] */
    private array $accessorMethods = [];

    private string $classname = '';

    private bool $genCompleted = false;

    private TraitAccessor $traitAccessor;

    private NodeFinder $nodeFinder;

    /** @var Property[] */
    private array $originalProperties = [];

    /** @var string[] */
    private array $originalMethods = [];

    public function __construct(
        private bool $genMethod,
        private CommentProcessor $commentProcessor,
    ) {
        $this->nodeFinder = new NodeFinder();
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof Class_ || empty($node->attrGroups)) {
            return null;
        }

        $this->classname = '\\' . $node->namespacedName->toString();
        $attributeProcessor = new AttributeProcessor($node);
        if (! $attributeProcessor->shouldProcess() || ! $this->parsePropertiesAndMethods($node)) {
            return null;
        }

        $this->genAccessors($node->namespacedName->toString(), $attributeProcessor);

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

        return $this->addIncludeForTraitAccessor($nodes);
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

    /**
     * Parse properties and methods from class node.
     */
    private function parsePropertiesAndMethods(Class_ $node): bool
    {
        $this->originalProperties = $this->nodeFinder->findInstanceOf($node, Property::class);
        $originalClassMethods = $this->nodeFinder->findInstanceOf($node, ClassMethod::class);

        /** @var ClassMethod[] $originalClassMethods */
        foreach ($originalClassMethods as $method) {
            $this->originalMethods[] = $method->name->name;
            if ($method->name->name == '__construct') {
                $this->addPromotedParamsToProperties($method);
            }
        }

        return ! empty($this->originalProperties);
    }

    private function addPromotedParamsToProperties(ClassMethod $method): void
    {
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

    /**
     * Generate accessor methods and trait accessor.
     */
    private function genAccessors(
        string $classname,
        AttributeProcessor $attributeProcessor,
    ): void {
        foreach ($this->originalProperties as $property) {
            if ($attributeProcessor->isIgnored($property)) {
                continue;
            }

            $this->accessorMethods = array_merge(
                $this->accessorMethods,
                $this->createMethodsFromProperties($classname, $property, $attributeProcessor)
            );
        }

        $this->genTraitAccessor();
    }

    private function createMethodsFromProperties(
        string $classname,
        Property $property,
        AttributeProcessor $attributeProcessor
    ): array {
        $methods = [];
        foreach ($property->props as $prop) {
            $methods = array_merge(
                $methods,
                MethodFactory::createFromProperty(
                    classname: $classname,
                    property: $prop,
                    propertyType: $property->type,
                    propertyDocComment: $this->commentProcessor->buildDocNode($property),
                    attributeProcessor: $attributeProcessor
                )
            );
        }

        return $methods;
    }

    private function genTraitAccessor(): void
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

        $this->addAttributes($class, $node);
        $this->addImplements($class, $node);

        $newNode = $class->getNode();
        $newNode->stmts = array_merge($newNode->stmts, $node->stmts);

        return $newNode;
    }

    private function addAttributes(\PhpParser\Builder\Class_ $class, Class_ $node): void
    {
        foreach ($node->attrGroups as $attrGroup) {
            if ($this->shouldIgnoreAttribute($attrGroup)) {
                continue;
            }
            $class->addAttribute($attrGroup);
        }
    }

    private function shouldIgnoreAttribute(AttributeGroup $attrGroup): bool
    {
        foreach ($attrGroup->attrs as $attr) {
            if ($attr->name->toString() == Data::class) {
                return true;
            }
        }
        return false;
    }

    private function addImplements(\PhpParser\Builder\Class_ $class, Class_ $node): void
    {
        foreach ($node->implements as $implement) {
            $class->implement($implement);
        }
    }

    /**
     * Add include statement for trait accessor.
     */
    private function addIncludeForTraitAccessor(array $nodes): array
    {
        $namespace = (new NodeFinder())->findFirstInstanceOf($nodes, Namespace_::class);
        $includePath = File::ACCESSOR . DIRECTORY_SEPARATOR . $this->traitAccessor->getClassName() . '.php';
        $include = new Expression(new Include_(new String_($includePath), Include_::TYPE_INCLUDE_ONCE));
        array_unshift($namespace->stmts, $include);

        return $nodes;
    }
}
