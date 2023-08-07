<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Attribute\Map\AccessorType;
use PhpAccessor\Processor\Method\AbstractMethod;
use PhpAccessor\Processor\Method\AccessorMethod;
use PhpAccessor\Processor\Method\FieldMetadataBuilder;
use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Processor\Method\SetterMethod;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

class MethodFactory
{
    /** @var AbstractMethod[] */
    private static array $methodHandlers = [
        AccessorType::GETTER => GetterMethod::class,
        AccessorType::SETTER => SetterMethod::class,
    ];

    /**
     * @return AccessorMethod[]
     */
    public static function createFromField(
        string $classname,
        PropertyProperty $property,
        null|Identifier|Name|ComplexType $propertyType,
        null|PhpDocNode $propertyDocComment,
        AttributeProcessor $attributeProcessor
    ): array {
        $accessorMethods = [];
        $builder = new FieldMetadataBuilder($classname, $property, $propertyType, $propertyDocComment, $attributeProcessor);
        $fieldMetadata = $builder->build();

        if ($attributeProcessor->shouldGenerateGetter()) {
            $accessorMethods[] = static::$methodHandlers[AccessorType::GETTER]::createFromFieldMetadata($fieldMetadata);
        }

        if ($attributeProcessor->shouldGenerateSetter()) {
            $accessorMethods[] = static::$methodHandlers[AccessorType::SETTER]::createFromFieldMetadata($fieldMetadata);
        }

        return $accessorMethods;
    }
}
