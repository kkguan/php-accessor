<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Attribute;

use PhpAccessor\Attribute\Map\NamingConvention as NamingConventionMap;

class NamingConvention
{
    private int $value = NamingConventionMap::NONE;

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function buildName(string $fieldName): string
    {
        return match ($this->value) {
            NamingConventionMap::LOWER_CAMEL_CASE => $this->camelize($fieldName, true),
            NamingConventionMap::UPPER_CAMEL_CASE => $this->camelize($fieldName),
            default => ucfirst($fieldName),
        };
    }

    private function camelize($str, $low = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($str))));

        return $low ? lcfirst($str) : $str;
    }
}
