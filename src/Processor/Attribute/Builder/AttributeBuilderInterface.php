<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpAccessor\Processor\Attribute\AttributeHandlerInterface;
use PhpParser\Node\Attribute;

interface AttributeBuilderInterface
{
    public function setAttribute(Attribute $attribute): static;

    public function build(): ?AttributeHandlerInterface;
}
