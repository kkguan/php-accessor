<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method\Generator\Setter;

use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadata;
use PhpAccessor\Processor\Method\Generator\GeneratorInterface;
use PhpAccessor\Processor\Method\SetterMethod;

class ParameterTypeGenerator implements GeneratorInterface
{
    /**
     * @param SetterMethod $accessorMethod
     */
    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void
    {
        $types = $fieldMetadata->getFieldTypes() ?: ['mixed'];
        $accessorMethod->setParameterTypes($types);
    }
}
