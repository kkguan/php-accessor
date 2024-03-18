<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;

#[Data]
class GetterMethodComment
{
    private string $name;

    private string $age;

    /** @var int */
    private $id;

    /** @var FooSub[] */
    private $array1;

    /** @var FooSub[] */
    private array $array2;

    /** @var string[] */
    private array $array3;

    /** @var array<string> */
    private array $array4;

    /** @var array<FooSub> */
    private array $array5;

    /** @var array<string, FooSub> */
    private array $array6;

    /**
     * @var int
     * @var string
     */
    private $foo;

    private FooSub $fooSub;
}
