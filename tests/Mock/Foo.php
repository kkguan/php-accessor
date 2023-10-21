<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;
use PhpAccessor\Attribute\Map\NamingConvention;
use PhpAccessor\Attribute\Overlook;

#[TestAttribute]
#[Data(namingConvention: NamingConvention::UPPER_CAMEL_CASE)]
class Foo extends SuperFoo implements FooInterface1, FooInterface2
{
    public const AAAA = 1;

    /**
     * @var FooSub[]
     */
    private array $names;

    #[Overlook]
    #[Overlook22]
    private string $ignore;

    private string|array|FooSub $name;

    private ?FooSub $name2;

    /**
     * @var string
     */
    private $test_id_2;

    public function call()
    {
        $this->setName([]);
        $this->setTestId2(213123);
    }
}
