<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute;

use PhpAccessor\Attribute\Map\AccessorType as AccessorTypeMap;

class AccessorType
{
    private string $value = AccessorTypeMap::BOTH;

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): AccessorType
    {
        $this->value = $value;
        return $this;
    }

    public function shouldGenerateGetter(): bool
    {
        if ($this->value == AccessorTypeMap::BOTH || $this->value == AccessorTypeMap::GETTER) {
            return true;
        }

        return false;
    }

    public function shouldGenerateSetter(): bool
    {
        if ($this->value == AccessorTypeMap::BOTH || $this->value == AccessorTypeMap::SETTER) {
            return true;
        }

        return false;
    }
}
