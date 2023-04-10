<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Mock;

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
}
