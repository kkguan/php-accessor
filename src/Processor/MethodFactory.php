<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadataBuilder;
use PhpAccessor\Processor\Method\Generator\Getter\GetterBodyGenerator;
use PhpAccessor\Processor\Method\Generator\Getter\GetterMethodNameGenerator;
use PhpAccessor\Processor\Method\Generator\Getter\MethodCommentGenerator;
use PhpAccessor\Processor\Method\Generator\Getter\ReturnTypeGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\ParameterTypeGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\SetterBodyGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\SetterMethodNameGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\SetterReturnTypeGenerator;
use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Processor\Method\SetterMethod;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

class MethodFactory
{
    /**
     * @return AccessorMethodInterface[]
     */
    public static function createFromProperty(
        string $classname,
        PropertyProperty $property,
        null|Identifier|Name|ComplexType $propertyType,
        null|PhpDocNode $propertyDocComment,
        AttributeProcessor $attributeProcessor
    ): array {
        $accessorMethods = [];
        $builder = new FieldMetadataBuilder($classname, $property, $propertyType, $propertyDocComment);
        $fieldMetadata = $builder->build();

        if ($attributeProcessor->shouldGenerateGetter()) {
            $getter = new GetterMethod();
            $getter->setFieldMetadata($fieldMetadata);
            $getter->addGenerator(new GetterMethodNameGenerator($attributeProcessor));
            $getter->addGenerator(new MethodCommentGenerator());
            $getter->addGenerator(new ReturnTypeGenerator($attributeProcessor));
            $getter->addGenerator(new GetterBodyGenerator($attributeProcessor));
            $getter->generate();
            $accessorMethods[] = $getter;
        }

        if ($attributeProcessor->shouldGenerateSetter()) {
            $setter = new SetterMethod();
            $setter->setFieldMetadata($fieldMetadata);
            $setter->addGenerator(new SetterMethodNameGenerator($attributeProcessor));
            $setter->addGenerator(new ParameterTypeGenerator());
            $setter->addGenerator(new SetterReturnTypeGenerator());
            $setter->addGenerator(new SetterBodyGenerator());
            $setter->generate();
            $accessorMethods[] = $setter;
        }

        return $accessorMethods;
    }
}
