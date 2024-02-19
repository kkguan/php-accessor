<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;

abstract class AbstractAttributeHandler implements AttributeHandlerInterface
{
    /**
     * @param Node[] $nodes
     */
    public function processAttributes(array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->processNode($node);
        }
    }

    protected function processNode(Node $node): void
    {
        if ($node instanceof Attribute) {
            $this->processAttribute($node);
        } elseif ($node instanceof Property) {
            $this->processProperty($node);
        }
    }

    protected function processProperty(Property $property): void
    {
        $nodeFinder = new NodeFinder();
        /** @var Attribute[] $attributes */
        $attributes = $nodeFinder->findInstanceOf($property->attrGroups, Attribute::class);
        foreach ($attributes as $attribute) {
            $this->processAttribute($attribute, $property);
        }
    }
}
