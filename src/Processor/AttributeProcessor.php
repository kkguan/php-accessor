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

class AttributeProcessor
{
    private Data $data;

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
    public function ignoreProperty(array $attributes): bool
    {
        $overlookBuilder = ( new OverlookBuilder())
            ->setAttributes($attributes);
        $overlook = $overlookBuilder->build();
        if ($overlook && $overlook->isOverlook()) {
            return true;
        }

        return false;
    }
}
