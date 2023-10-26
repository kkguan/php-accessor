<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpAccessor\Attribute\Map\AccessorType as AccessorTypeMap;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

/**
 * @internal
 */
class AccessorTypeHandler implements ParameterHandlerInterface
{
    private string $value = AccessorTypeMap::BOTH;

    public function processParameter(Arg $parameter): void
    {
        if ($parameter->name->name != 'accessorType') {
            return;
        }

        $parameterValue = $parameter->value;
        if (! ($parameterValue instanceof ClassConstFetch) || ! ($parameterValue->name instanceof Identifier)) {
            return;
        }

        $value = match ($parameterValue->name->name) {
            'BOTH' => AccessorTypeMap::BOTH,
            'GETTER' => AccessorTypeMap::GETTER,
            'SETTER' => AccessorTypeMap::SETTER,
            default => null,
        };
        $value && $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): AccessorTypeHandler
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
