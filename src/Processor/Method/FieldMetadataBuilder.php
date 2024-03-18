<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

class FieldMetadataBuilder
{
    private FieldMetadata $fieldMetadata;

    public function __construct(
        private string $classname,
        private PropertyProperty $property,
        private null|Identifier|Name|ComplexType $propertyType,
        private null|PhpDocNode $propertyDocComment,
    ) {
        $this->fieldMetadata = new FieldMetadata();
    }

    public function build(): FieldMetadata
    {
        $this->fieldMetadata->setClassname($this->classname);
        $this->fieldMetadata->setFieldName($this->property->name->toString());
        $this->fieldMetadata->setComment($this->propertyDocComment);
        $this->buildFieldTypes($this->propertyType);

        return $this->fieldMetadata;
    }

    private function buildFieldTypes($propertyType): void
    {
        if ($propertyType == null) {
            return;
        }

        if ($propertyType instanceof Identifier) {
            $this->fieldMetadata->addFieldType($propertyType->name);

            return;
        }

        if ($propertyType instanceof NullableType) {
            $this->fieldMetadata->addFieldType('null');
            $this->buildFieldTypes($propertyType->type);

            return;
        }

        if ($propertyType instanceof Name) {
            $this->fieldMetadata->addFieldType('\\' . $propertyType->toString());

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
}
