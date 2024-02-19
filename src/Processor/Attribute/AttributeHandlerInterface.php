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

interface AttributeHandlerInterface
{
    /**
     * @param Node[] $nodes
     */
    public function processAttributes(array $nodes): void;

    public function processAttribute(Attribute $attribute, ?Property $property = null): void;
}
