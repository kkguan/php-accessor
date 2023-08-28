<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpAccessor\Attribute\Overlook as OverlookAttribute;
use PhpAccessor\Processor\Attribute\OverlookHandler;
use PhpParser\Node\Attribute;

class OverlookBuilder implements AttributeBuilderInterface
{
    /**
     * @var Attribute[]
     */
    private array $attributes;

    private Attribute $attribute;

    public function setAttribute(Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function build(): ?OverlookHandler
    {
        if ($this->attribute->name->toString() != OverlookAttribute::class) {
            return null;
        }

        return new OverlookHandler();
    }
}
