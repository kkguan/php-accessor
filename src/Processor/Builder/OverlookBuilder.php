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
    private Attribute $attribute;

    public function setAttribute(Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function build(): ?Overlook
    {
        if (OverlookAttribute::class != $this->attribute->name->toString()) {
            return null;
        }

        return new Overlook();
    }
}
