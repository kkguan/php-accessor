<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpAccessor\Attribute\Map\PrefixConvention;
use PhpAccessor\Processor\Method\AccessorMethodType;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

/**
 * @internal
 */
class PrefixConventionHandler implements ParameterHandlerInterface
{
    private int $value = PrefixConvention::GET_SET;

    public function processParameter(Arg $parameter): void
    {
        if ($parameter->name->name != 'prefixConvention') {
            return;
        }

        $parameterValue = $parameter->value;
        if (! ($parameterValue instanceof ClassConstFetch) || ! ($parameterValue->name instanceof Identifier)) {
            return;
        }

        $value = match ($parameterValue->name->name) {
            'GET_SET' => PrefixConvention::GET_SET,
            'BOOLEAN_IS' => PrefixConvention::BOOLEAN_IS,
            default => null,
        };
        $value && $this->value = $value;
    }

    public function setValue(int $value): PrefixConventionHandler
    {
        $this->value = $value;
        return $this;
    }

    public function buildPrefix(array $fieldType, string $accessorMethodType): string
    {
        return match ($this->value) {
            PrefixConvention::GET_SET => $this->buildGetSetPrefix($accessorMethodType),
            PrefixConvention::BOOLEAN_IS => $this->buildBooleanIsPrefix($fieldType, $accessorMethodType),
            default => '',
        };
    }

    private function buildGetSetPrefix(string $accessorMethodType): string
    {
        return $accessorMethodType === AccessorMethodType::GETTER ? 'get' : 'set';
    }

    private function buildBooleanIsPrefix(array $fieldType, string $accessorMethodType): string
    {
        $prefix = $this->buildGetSetPrefix($accessorMethodType);
        if (in_array('bool', $fieldType) && $accessorMethodType == AccessorMethodType::GETTER) {
            $prefix = 'is';
        }

        return $prefix;
    }
}
