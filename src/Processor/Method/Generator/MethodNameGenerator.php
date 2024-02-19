<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method\Generator;

use PhpAccessor\Processor\AttributeProcessor;
use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadata;

class MethodNameGenerator implements GeneratorInterface
{
    public function __construct(
        protected AttributeProcessor $attributeProcessor
    ) {
    }

    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void
    {
        $methodName = $this->attributeProcessor->buildMethodNameFromField(
            fieldName: $fieldMetadata->getFieldName(),
            fieldType: $fieldMetadata->getFieldTypes(),
            accessorMethodType: $accessorMethod->getName()
        );
        $accessorMethod->setMethodName($methodName);
    }
}
