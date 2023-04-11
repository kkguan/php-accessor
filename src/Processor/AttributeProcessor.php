<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Attribute\Data;
use PhpAccessor\Processor\Builder\OverlookBuilder;
use PhpParser\Node\Attribute;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

class AttributeProcessor
{
    private Data $data;

    /** @var array<array{prop:PropertyProperty, type:Identifier|Name|ComplexType|null}> */
    private array $pendingProperties = [];

    private bool $isPending = false;

    public function setData(?Data $data): self
    {
        if (empty($data)) {
            return $this;
        }

        $this->data = $data;
        $this->isPending = true;

        return $this;
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

    /**
     * @param Attribute[] $attributes
     */
    public function buildPropertyAttributes(Property $property, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $overlookBuilder = new OverlookBuilder();
            $overlookBuilder->setAttribute($attribute);
            $overlook = $overlookBuilder->build();
        }

        foreach ($property->props as $prop) {
            if (isset($overlook) && $overlook->isOverlook()) {
                continue;
            }

            $this->pendingProperties[] = ['prop' => $prop, 'type' => $property->type, 'doc' => $property->getDocComment()];
        }
    }

    /**
     * @return array<array{prop:PropertyProperty, type:Identifier|Name|ComplexType|null}>
     */
    public function getPendingProperties(): array
    {
        return $this->pendingProperties;
    }
}
