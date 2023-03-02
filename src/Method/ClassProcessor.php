<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Method;

use PhpAccessor\Attribute\Data;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

class ClassProcessor extends NodeVisitorAbstract
{
    /** @var AccessorMethod[] */
    private array $accessorMethods = [];

    private string $classname = '';

    private bool $genCompleted = false;

    public function __construct(
        private bool $genMethod
    ) {
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Class_ || empty($node->attrGroups)) {
            return;
        }

        $needProcess = false;
        $this->classname = '\\'.$node->namespacedName->toString();
        foreach ($node->attrGroups as &$classAttribute) {
            foreach ($classAttribute as &$attrs) {
                /** @var Attribute $attr */
                foreach ($attrs as $k => $attr) {
                    if (Data::class == $attr->name->toString()) {
                        $needProcess = true;
//                        unset($attrs[$k]);
                    }
                }
            }
        }
        if (!$needProcess) {
            return;
        }

        $nodeFinder = new NodeFinder();
        /** @var Property[] $properties */
        $properties = $nodeFinder->findInstanceOf($node, Property::class);
        if (empty($properties)) {
            return;
        }

        /** @var ClassMethod[] $originalClassMethods */
        $originalClassMethods = $nodeFinder->findInstanceOf($node, ClassMethod::class);
        $originalClassMethodNames = [];
        foreach ($originalClassMethods as $originalClassMethod) {
            $originalClassMethodNames[] = $originalClassMethod->name->toString();
        }
        foreach ($properties as $property) {
            foreach ($property->props as $prop) {
                $this->accessorMethods = array_merge(
                    $this->accessorMethods,
                    MethodFactory::createFromField($node->namespacedName->toString(), $prop, $property->type)
                );
            }
        }

        if (empty($this->accessorMethods)) {
            return;
        }

        if (!$this->genMethod) {
            return;
        }

        foreach ($this->accessorMethods as $accessorMethod) {
            if (\in_array($accessorMethod->getMethodName(), $originalClassMethodNames)) {
                continue;
            }
            $node->stmts[] = $accessorMethod->buildMethod();
        }
        $node = new Class_($node->name, [
            'stmts' => $node->stmts,
        ]);
        $this->genCompleted = true;

        return null;
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
}
