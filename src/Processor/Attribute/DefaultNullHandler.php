<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute;

use PhpAccessor\Attribute\DefaultNull;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;

/**
 * @internal
 */
class DefaultNullHandler extends AbstractAttributeHandler
{
    private bool $isDefaultNull = false;

    private array $propertyIsDefaultNull = [];

    public function processAttribute(Attribute $attribute, ?Property $property = null): void
    {
        if ($attribute->name->toString() != DefaultNull::class) {
            return;
        }

        if ($property === null) {
            $this->isDefaultNull = true;
            return;
        }

        foreach ($property->props as $prop) {
            $this->propertyIsDefaultNull[$prop->name->name] = true;
        }
    }

    public function isDefaultNull(string $propertyName): bool
    {
        if ($this->isDefaultNull) {
            return true;
        }

        return isset($this->propertyIsDefaultNull[$propertyName]) && $this->propertyIsDefaultNull[$propertyName];
    }
}
