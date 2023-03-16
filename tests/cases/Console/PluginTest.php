<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Console;

use PhpAccessor\Test\Mock\Foo;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function test1()
    {
        $foo = new Foo();
        $foo
            ->setName2([3333])
            ->setName(123123);
    }
}
