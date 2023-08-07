<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

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
            if ($attribute->name->toString() == OverlookAttribute::class) {
                return new Overlook();
            }
        }

        return null;
    }
}
