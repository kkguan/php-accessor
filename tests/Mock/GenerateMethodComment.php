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
class GenerateMethodComment
{
    /**
     * @var int
     */
    public $id;

    public string $name = '';

    public int $age;

    /**
     * @var FooSub[]
     */
    public $array1;

    /**
     * @var FooSub[]
     */
    public array $array2;

    /**
     * @var string[]
     */
    public array $array3;

    /**
     * @var array<string>
     */
    public array $array4;

    /**
     * @var array<FooSub>
     */
    public array $array5;

    /**
     * @var array<string, FooSub>
     */
    public array $array6;

    /**
     * @var null|array<string, FooSub>
     */
    public array $array7;

    /**
     * @var array{user: Foo, orders: array<FooSub>}
     */
    public array $array8;

    /**
     * @var int
     * @var string
     */
    public $foo;
}
