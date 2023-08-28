<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpAccessor\Attribute\DefaultNull;
use PhpAccessor\Processor\Attribute\DefaultNullHandler;
use PhpParser\Node\Attribute;

class DefaultNullBuilder implements AttributeBuilderInterface
{
    private Attribute $attribute;

    public function setAttribute(Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function build(): ?DefaultNullHandler
    {
        if ($this->attribute->name->toString() != DefaultNull::class) {
            return null;
        }

        return new DefaultNullHandler();
    }
}
