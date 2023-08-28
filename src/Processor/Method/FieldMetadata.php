<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

class FieldMetadata
{
    private string $classname;

    private string $fieldName;

    /** @var string[] */
    private array $fieldTypes = [];

    private ?PhpDocNode $comment;

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function setClassname(string $classname): FieldMetadata
    {
        $this->classname = $classname;
        return $this;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): FieldMetadata
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    public function addFieldType(string $fieldType): FieldMetadata
    {
        $this->fieldTypes[] = $fieldType;
        return $this;
    }

    public function setFieldTypes(array $fieldTypes): FieldMetadata
    {
        $this->fieldTypes = $fieldTypes;
        return $this;
    }

    public function getComment(): ?PhpDocNode
    {
        return $this->comment;
    }

    public function setComment(?PhpDocNode $comment): FieldMetadata
    {
        $this->comment = $comment;
        return $this;
    }
}
