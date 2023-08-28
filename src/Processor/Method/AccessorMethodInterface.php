<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method;

use JsonSerializable;
use PhpParser\Node\Stmt\ClassMethod;

interface AccessorMethodInterface extends JsonSerializable
{
    public function setReturnTypes(array $returnTypes): static;

    public function setMethodName(string $methodName): static;

    public function setMethodComment(string $methodComment): static;

    public function setFieldTypes(array $fieldTypes): static;

    public function getName(): string;

    public function getClassName(): string;

    public function getFieldName(): string;

    /**
     * @return string[]
     */
    public function getFieldTypes(): array;

    public function getMethodName(): string;

    public function getMethodComment(): string;

    public function buildMethod(): ClassMethod;
}
