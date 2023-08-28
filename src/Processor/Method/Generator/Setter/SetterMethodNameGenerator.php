<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method\Generator\Setter;

use PhpAccessor\Processor\AttributeProcessor;
use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadata;
use PhpAccessor\Processor\Method\Generator\GeneratorInterface;

class SetterMethodNameGenerator implements GeneratorInterface
{
    public function __construct(
        protected AttributeProcessor $attributeProcessor
    ) {
    }

    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void
    {
        $accessorMethod->setMethodName('set' . $this->attributeProcessor->buildMethodSuffixFromField($fieldMetadata->getFieldName()));
    }
}
