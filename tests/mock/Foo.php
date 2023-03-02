<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;

#[Data]
class Foo
{
    public const AAAA = 1;

//    private int $id1;
    // //    private int $id3,$id4;
//
//    private ?string $string;
//
//    private $mixd;

    private string|array|FooSub $name;

//    private FooSub $fooSub;
}
