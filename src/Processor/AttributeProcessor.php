<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Attribute\Builder\DataBuilder;
use PhpAccessor\Processor\Attribute\Builder\OverlookBuilder;
use PhpAccessor\Processor\Attribute\Data;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;

class AttributeProcessor
{
    private Data $data;

    private bool $isPending = false;

    private array $ignoreProperties = [];

    private NodeFinder $nodeFinder;

    public function __construct(Node $node)
    {
        $this->nodeFinder = new NodeFinder();
        $this->parseClassAttribute($node);
        $this->parsePropertyAttribute($node);
    }

    public function isPending(): bool
    {
        return $this->isPending;
    }

    public function buildMethodSuffixFromField(string $fieldName): string
    {
        $namingConvention = $this->data->getNamingConvention();

        return $namingConvention->buildName($fieldName);
    }

    public function ignoreProperty(Property $property): bool
    {
        $ignore = true;
        foreach ($property->props as $prop) {
            if (! isset($this->ignoreProperties[$prop->name->name])) {
                $ignore = false;
                break;
            }
        }

        return $ignore;
    }

    public function shouldGenerateGetter(): bool
    {
        return $this->data->getAccessorType()->shouldGenerateGetter();
    }

    public function shouldGenerateSetter(): bool
    {
        return $this->data->getAccessorType()->shouldGenerateSetter();
    }

    private function parseClassAttribute(Node $node): void
    {
        /** @var Attribute[] $attributes */
        $attributes = $this->nodeFinder->findInstanceOf($node->attrGroups, Attribute::class);
        foreach ($attributes as $attribute) {
            $dataBuilder = new DataBuilder();
            $data = $dataBuilder->setAttribute($attribute)->build();
            if (empty($data)) {
                continue;
            }

            $this->data = $data;
            $this->isPending = true;
            break;
        }
    }

    private function parsePropertyAttribute(Node $node): void
    {
        /** @var Property[] $properties */
        $properties = $this->nodeFinder->findInstanceOf($node, Property::class);
        if (empty($properties)) {
            return;
        }

        $overlookBuilder = new OverlookBuilder();
        foreach ($properties as $property) {
            /** @var Attribute[] $attributes */
            $attributes = $this->nodeFinder->findInstanceOf($property->attrGroups, Attribute::class);
            $overlookBuilder->setAttributes($attributes);
            $overlook = $overlookBuilder->build();
            if ($overlook && $overlook->isOverlook()) {
                foreach ($property->props as $prop) {
                    $this->ignoreProperties[$prop->name->name] = 1;
                }
            }
        }
    }
}
