<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Method;

use JsonSerializable;
use PhpParser\Node\Stmt\ClassMethod;

interface AccessorMethod extends JsonSerializable
{
    public function getName(): string;

    public function getClassName(): string;

    public function getFieldName(): string;

    /**
     * @return string[]
     */
    public function getFieldTypes(): array;

    public function getMethodName(): string;

    public function buildMethod(): ClassMethod;
}
