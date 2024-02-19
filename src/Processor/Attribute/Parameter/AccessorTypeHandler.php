<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpAccessor\Attribute\Map\AccessorType as AccessorTypeMap;
use PhpParser\Node\Expr;

/**
 * @internal
 */
class AccessorTypeHandler extends AbstractParameterHandler
{
    protected mixed $config = AccessorTypeMap::BOTH;

    public function shouldGenerateGetter(): bool
    {
        if ($this->config == AccessorTypeMap::BOTH || $this->config == AccessorTypeMap::GETTER) {
            return true;
        }

        return false;
    }

    public function shouldGenerateSetter(): bool
    {
        if ($this->config == AccessorTypeMap::BOTH || $this->config == AccessorTypeMap::SETTER) {
            return true;
        }

        return false;
    }

    protected function shouldProcess(string $parameterName, Expr $parameterValue): bool
    {
        return $parameterName == 'accessorType';
    }

    protected function getClassName(): string
    {
        return AccessorTypeMap::class;
    }
}
