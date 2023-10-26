<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Method\AbstractAccessorMethod;
use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\AccessorMethodType;
use PhpAccessor\Processor\Method\FieldMetadataBuilder;
use PhpAccessor\Processor\Method\Generator\GeneratorInterface;
use PhpAccessor\Processor\Method\Generator\Getter\GetterBodyGenerator;
use PhpAccessor\Processor\Method\Generator\Getter\MethodCommentGenerator;
use PhpAccessor\Processor\Method\Generator\Getter\ReturnTypeGenerator;
use PhpAccessor\Processor\Method\Generator\MethodNameGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\ParameterTypeGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\SetterBodyGenerator;
use PhpAccessor\Processor\Method\Generator\Setter\SetterReturnTypeGenerator;
use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Processor\Method\SetterMethod;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

/**
 * @internal
 */
class MethodFactory
{
    /**
     * @var array{string: array{processor: AbstractAccessorMethod::class, generators: GeneratorInterface[]}}
     */
    private static array $generatorConfig = [
        AccessorMethodType::GETTER => [
            'processor' => GetterMethod::class,
            'generators' => [
                MethodNameGenerator::class,
                MethodCommentGenerator::class,
                ReturnTypeGenerator::class,
                GetterBodyGenerator::class,
            ],
        ],
        AccessorMethodType::SETTER => [
            'processor' => SetterMethod::class,
            'generators' => [
                MethodNameGenerator::class,
                ParameterTypeGenerator::class,
                SetterReturnTypeGenerator::class,
                SetterBodyGenerator::class,
            ],
        ],
    ];

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
        foreach (self::$generatorConfig as $accessorMethodType => $generators) {
            if (! $attributeProcessor->shouldGenMethod($accessorMethodType)) {
                continue;
            }
            /** @var AbstractAccessorMethod $processor */
            $processor = new $generators['processor']();
            $processor->setFieldMetadata($fieldMetadata);
            foreach ($generators['generators'] as $generator) {
                $processor->addGenerator(new $generator($attributeProcessor));
            }
            $processor->generate();
            $accessorMethods[] = $processor;
        }

        return $accessorMethods;
    }
}
