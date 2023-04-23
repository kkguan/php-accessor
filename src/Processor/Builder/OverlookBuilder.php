<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Builder;

use PhpAccessor\Attribute\Overlook as OverlookAttribute;
use PhpAccessor\Processor\Attribute\Overlook;
use PhpParser\Node\Attribute;

class OverlookBuilder
{
    /**
     * @var Attribute[]
     */
    private array $attributes;

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function build(): ?Overlook
    {
        foreach ($this->attributes as $attribute) {
            if (OverlookAttribute::class == $attribute->name->toString()) {
                return new Overlook();
            }
        }

        return null;
    }
}
