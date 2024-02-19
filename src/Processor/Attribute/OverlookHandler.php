<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute;

use PhpAccessor\Attribute\Overlook as OverlookAttribute;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;

/**
 * @internal
 */
class OverlookHandler extends AbstractAttributeHandler
{
    private array $propertyIsOverlook = [];

    public function processAttribute(Attribute $attribute, ?Property $property = null): void
    {
        if ($attribute->name->toString() != OverlookAttribute::class || $property === null) {
            return;
        }

        foreach ($property->props as $prop) {
            $this->propertyIsOverlook[$prop->name->name] = true;
        }
    }

    public function isOverlook(Property $property): bool
    {
        foreach ($property->props as $prop) {
            if (isset($this->propertyIsOverlook[$prop->name->name])
                && $this->propertyIsOverlook[$prop->name->name]
            ) {
                return true;
            }
        }

        return false;
    }
}
