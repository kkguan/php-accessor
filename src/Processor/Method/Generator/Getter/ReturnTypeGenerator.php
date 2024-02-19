<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method\Generator\Getter;

use PhpAccessor\Processor\AttributeProcessor;
use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadata;
use PhpAccessor\Processor\Method\Generator\GeneratorInterface;
use PhpAccessor\Processor\Method\GetterMethod;

class ReturnTypeGenerator implements GeneratorInterface
{
    public function __construct(
        protected AttributeProcessor $attributeProcessor
    ) {
    }

    /**
     * @param GetterMethod $accessorMethod
     */
    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void
    {
        if (empty($fieldMetadata->getFieldTypes())) {
            $types = ['mixed'];
        } else {
            $types = $fieldMetadata->getFieldTypes();
            if ($this->attributeProcessor->isDefaultNull($fieldMetadata->getFieldName())
                && ! in_array('null', $types)
                && ! in_array('mixed', $types)) {
                $types[] = 'null';
            }
        }

        $accessorMethod->setReturnTypes($types);
    }
}
