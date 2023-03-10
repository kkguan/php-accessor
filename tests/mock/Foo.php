<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;
use PhpAccessor\Attribute\Map\NamingConvention;
use PhpAccessor\Attribute\Overlook;

#[Data(namingConvention: NamingConvention::UPPER_CAMEL_CASE)]
class Foo
{
    public const AAAA = 1;

//    private int $id1;
    // //    private int $id3,$id4;
//
//    private ?string $string;
//
//    private $mixd;
    #[Overlook]
    private string $ignore;

    private string|array|FooSub $name;

    private ?FooSub $name2;

    private $test_id_2;

    public function call()
    {
        $this->setName(222);
        $this->setTestId2(213123);
    }

//    private FooSub $fooSub;
}
