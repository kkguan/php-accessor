<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

abstract class AbstractMethod implements AccessorMethod
{
    protected string $name = '';

    protected string $className;

    protected string $fieldName;

    protected ?PhpDocNode $fieldComment = null;

    /** @var string[] */
    protected array $fieldTypes;

    protected string $methodName;

    protected string $methodSuffix;

    protected string $methodComment = '';

    public function __construct($className, $fieldName, $fieldTypes, $fieldComment)
    {
        $this->className = $className;
        $this->fieldName = $fieldName;
        $this->fieldTypes = $fieldTypes;
        $this->fieldComment = $fieldComment;
    }

    public function jsonSerialize(): array
    {
        $json = [];
        foreach ($this as $key => $value) {
            $json[$key] = $value;
        }

        return $json;
    }

    public static function createFromFieldMetadata(FieldMetadata $fieldMetadata): static
    {
        $obj = new static($fieldMetadata->getClassname(),  $fieldMetadata->getFieldName(),$fieldMetadata->getFieldTypes(), $fieldMetadata->getComment());
        $obj->setMethodSuffix($fieldMetadata->getMethodSuffix());
        $obj->init();

        return $obj;
    }

    public function setMethodSuffix(string $methodSuffix): self
    {
        $this->methodSuffix = $methodSuffix;

        return $this;
    }

    abstract public function init();

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    public function getFieldComment(): string
    {
        return (string) $this->fieldComment;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethodComment(): string
    {
        return $this->methodComment;
    }
}
