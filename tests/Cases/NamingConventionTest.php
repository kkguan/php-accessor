<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\NamingConventionLowerCamelCase;
use PhpAccessor\Test\Mock\NamingConventionNone;
use PhpAccessor\Test\Mock\NamingConventionUpperCamelCase;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class NamingConventionTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                NamingConventionNone::class,
                ['getNamingConvention', 'setNamingConvention'],
            ],
            [
                NamingConventionLowerCamelCase::class,
                ['getnamingConvention', 'setnamingConvention'],
            ],
            [
                NamingConventionUpperCamelCase::class,
                ['getNamingConvention', 'setNamingConvention'],
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
