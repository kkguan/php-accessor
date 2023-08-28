<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\OnlyGetter;
use PhpAccessor\Test\Mock\OnlySetter;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AccessorTypeTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                OnlyGetter::class,
                'getId',
            ],
            [
                OnlySetter::class,
                'setId',
            ],
        ];
    }

    /**
     * @dataProvider  genProvider
     * @param mixed $classname
     * @param mixed $method
     */
    public function testGen(string $classname, string $method)
    {
        $generatedFiles = GeneratorHelper::genFromClass($classname);
        $this->assertNotEmpty($generatedFiles);
        $proxyFile = $generatedFiles[1];
        $classMethods = GeneratorHelper::getMethods($proxyFile);
        $this->assertCount(1, $classMethods);
        $this->assertSame($method, $classMethods[$method]['name']);
    }
}
