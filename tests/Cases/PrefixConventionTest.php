<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\PrefixConventionBooleanIs;
use PhpAccessor\Test\Mock\PrefixConventionGetSet;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PrefixConventionTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                PrefixConventionBooleanIs::class,
                ['isFoo', 'setFoo'],
            ],
            [
                PrefixConventionGetSet::class,
                ['getFoo', 'setFoo'],
            ],
        ];
    }

    /**
     * @dataProvider genProvider
     */
    public function testGen(string $classname, array $methods)
    {
        $generatedFiles = GeneratorHelper::genFromClass($classname);
        $this->assertNotEmpty($generatedFiles);
        $proxyFile = $generatedFiles[1];
        $classMethods = GeneratorHelper::getMethods($proxyFile);
        $this->assertCount(count($methods), $classMethods);
        foreach ($methods as $method) {
            $this->assertArrayHasKey($method, $classMethods);
        }
    }
}
