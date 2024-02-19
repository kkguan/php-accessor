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
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;

class SetterBodyGenerator implements GeneratorInterface
{
    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void
    {
        $fieldName = $fieldMetadata->getFieldName();
        $builder = new BuilderFactory();
        $thisField = $builder->propertyFetch($builder->var('this'), $fieldName);
        $fieldVar = $builder->var($fieldName);

        $accessorMethod->setBody([
            new Expression(new Assign($thisField, $fieldVar)),
            new Return_($builder->var('this')),
        ]);
    }
}
