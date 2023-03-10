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
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

use const DIRECTORY_SEPARATOR;

class ClassProcessor extends NodeVisitorAbstract
{
    /** @var AccessorMethod[] */
    private array $accessorMethods = [];

    private string $classname = '';

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
        if (!$node instanceof Class_ || empty($node->attrGroups)) {
            return null;
        }

        $this->classname = '\\' . $node->namespacedName->toString();
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
        // TODO:?????????
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
                MethodFactory::createFromField($classname, $pendingProperty['prop'], $pendingProperty['type'], $attributeProcessor)
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
}
