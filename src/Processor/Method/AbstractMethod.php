<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Method;

abstract class AbstractMethod implements AccessorMethod
{
    protected string $name = '';

    protected string $className;

    protected string $fieldName;

    /** @var string[] */
    protected array $fieldTypes;

    protected string $methodName;

    protected string $methodSuffix;

    public function __construct($className, $fieldName, $fieldTypes)
    {
        $this->className = $className;
        $this->fieldName = $fieldName;
        $this->fieldTypes = $fieldTypes;
    }

    public function jsonSerialize(): array
    {
        $json = [];
        foreach ($this as $key => $value) {
            $json[$key] = $value;
        }

        return $json;
    }

    public static function createFromBuilder(MethodElementBuilder $builder): static
    {
        $obj = new static($builder->getClassname(),  $builder->getFieldName(),$builder->getFieldTypes());
        $obj->setMethodSuffix($builder->getMethodSuffix());
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

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
