<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpAccessor\Attribute\Map\NamingConvention as NamingConventionMap;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

/**
 * @internal
 */
class NamingConventionHandler implements ParameterHandlerInterface
{
    private int $value = NamingConventionMap::NONE;

    public function processParameter(Arg $parameter): void
    {
        if ($parameter->name->name != 'namingConvention') {
            return;
        }

        $parameterValue = $parameter->value;
        if (! ($parameterValue instanceof ClassConstFetch) || ! ($parameterValue->name instanceof Identifier)) {
            return;
        }

        $value = match ($parameterValue->name->name) {
            'LOWER_CAMEL_CASE' => NamingConventionMap::LOWER_CAMEL_CASE,
            'UPPER_CAMEL_CASE' => NamingConventionMap::UPPER_CAMEL_CASE,
            default => null,
        };
        $value && $this->value = $value;
    }

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
