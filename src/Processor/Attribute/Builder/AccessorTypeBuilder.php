<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpAccessor\Attribute\Map\AccessorType as AccessorTypeMap;
use PhpAccessor\Processor\Attribute\AccessorType;

class AccessorTypeBuilder extends AttributeParameterBuilder
{
    public function getName(): string
    {
        return 'accessorType';
    }

    public function build(): AccessorType
    {
        $accessorType = new AccessorType();

        $value = match ($this->parameterValue) {
            'BOTH' => AccessorTypeMap::BOTH,
            'GETTER' => AccessorTypeMap::GETTER,
            'SETTER' => AccessorTypeMap::SETTER,
            default => null,
        };
        $value && $accessorType->setValue($value);

        return $accessorType;
    }
}
