<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpAccessor\Attribute\Map\NamingConvention as NamingConventionMap;
use PhpParser\Node\Expr;

/**
 * @internal
 */
class NamingConventionHandler extends AbstractParameterHandler
{
    protected mixed $config = NamingConventionMap::NONE;

    public function buildName(string $fieldName): string
    {
        return match ($this->config) {
            NamingConventionMap::LOWER_CAMEL_CASE => $this->camelize($fieldName, true),
            NamingConventionMap::UPPER_CAMEL_CASE => $this->camelize($fieldName),
            default => ucfirst($fieldName),
        };
    }

    protected function shouldProcess(string $parameterName, Expr $parameterValue): bool
    {
        return $parameterName == 'namingConvention';
    }

    protected function getClassName(): string
    {
        return NamingConventionMap::class;
    }

    private function camelize($str, $low = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($str))));

        return $low ? lcfirst($str) : $str;
    }
}
