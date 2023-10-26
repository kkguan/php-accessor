<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpParser\Node\Arg;

interface ParameterHandlerInterface
{
    public function processParameter(Arg $parameter): void;
}
