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
use PhpParser\Node\Expr;

/**
 * Handles the prefix convention for method names.
 *
 * @internal
 */
class PrefixConventionHandler extends AbstractParameterHandler
{
    protected mixed $config = PrefixConvention::GET_SET;

    public function buildPrefix(array $fieldType, string $accessorMethodType): string
    {
        return match ($this->config) {
            PrefixConvention::GET_SET => $this->buildGetSetPrefix($accessorMethodType),
            PrefixConvention::BOOLEAN_IS => $this->buildBooleanIsPrefix($fieldType, $accessorMethodType),
            default => '',
        };
    }

    protected function shouldProcess(string $parameterName, Expr $parameterValue): bool
    {
        return $parameterName == 'prefixConvention';
    }

    protected function getClassName(): string
    {
        return PrefixConvention::class;
    }

    private function buildGetSetPrefix(string $accessorMethodType): string
    {
        return $accessorMethodType === AccessorMethodType::GETTER ? 'get' : 'set';
    }

    private function buildBooleanIsPrefix(array $fieldType, string $accessorMethodType): string
    {
        return in_array('bool', $fieldType) && $accessorMethodType == AccessorMethodType::GETTER
            ? 'is'
            : $this->buildGetSetPrefix($accessorMethodType);
    }
}
