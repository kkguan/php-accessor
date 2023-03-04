<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Method\AbstractMethod;
use PhpAccessor\Processor\Method\AccessorMethod;
use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Processor\Method\MethodElementBuilder;
use PhpAccessor\Processor\Method\SetterMethod;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\PropertyProperty;

class MethodFactory
{
    /** @var AbstractMethod[] */
    private static array $methodHandlers = [
        GetterMethod::class,
        SetterMethod::class,
    ];

    /**
     * @return AccessorMethod[]
     */
    public static function createFromField(string $classname, PropertyProperty $property, null|Identifier|Name|ComplexType $propertyType): array
    {
        $accessorMethods = [];
        $builder = new MethodElementBuilder($classname, $property, $propertyType);
        $builder->build();
        foreach (static::$methodHandlers as $methodHandler) {
            $m = $methodHandler::createFromBuilder($builder);
            $accessorMethods[] = $m;
        }

        return $accessorMethods;
    }
}
