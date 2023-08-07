<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpAccessor\Attribute\Map\NamingConvention as NamingConventionMap;
use PhpAccessor\Processor\Attribute\NamingConvention;

class NamingConventionBuilder extends AttributeParameterBuilder
{
    public function getName(): string
    {
        return 'namingConvention';
    }

    public function build(): NamingConvention
    {
        $namingConvention = new NamingConvention();
        $value = match ($this->parameterValue) {
            'LOWER_CAMEL_CASE' => NamingConventionMap::LOWER_CAMEL_CASE,
            'UPPER_CAMEL_CASE' => NamingConventionMap::UPPER_CAMEL_CASE,
            default => null,
        };
        $value && $namingConvention->setValue($value);

        return $namingConvention;
    }
}
