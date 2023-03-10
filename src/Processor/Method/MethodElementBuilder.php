<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Method;

use PhpAccessor\Processor\AttributeProcessor;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\UnionType;

class MethodElementBuilder
{
    protected string $fieldName;

    /**
     * @var string[]
     */
    protected array $fieldTypes = [];

    protected string $methodSuffix;

    public function __construct(
        private string $classname,
        private PropertyProperty $property,
        private null|Identifier|Name|ComplexType $propertyType,
        private AttributeProcessor $attributeProcessor
    ) {
    }

    public function build(): void
    {
        $this->fieldName = $this->property->name->toString();
        $this->buildMethodName();
        $this->buildFieldTypes($this->propertyType);
    }

    private function buildMethodName(): void
    {
        $this->methodSuffix = $this->attributeProcessor->buildMethodSuffixFromField($this->fieldName);
    }

    private function buildFieldTypes($propertyType): void
    {
        if (null == $propertyType) {
            return;
        }

        if ($propertyType instanceof Identifier) {
            $this->fieldTypes[] = $propertyType->name;

            return;
        }

        if ($propertyType instanceof NullableType) {
            $this->fieldTypes[] = 'null';
            $this->buildFieldTypes($propertyType->type);

            return;
        }

        if ($propertyType instanceof Name) {
            $this->fieldTypes[] = '\\' . implode('\\', $propertyType->parts);

            return;
        }

        if ($propertyType instanceof IntersectionType) {
            foreach ($propertyType->types as $type) {
                $this->buildFieldTypes($type);
            }

            return;
        }

        if ($propertyType instanceof UnionType) {
            foreach ($propertyType->types as $type) {
                $this->buildFieldTypes($type);
            }

            return;
        }
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function getMethodSuffix(): string
    {
        return $this->methodSuffix;
    }
}
