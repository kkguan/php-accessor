<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\ConstructorPromotion;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConstructorPromotionTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                ConstructorPromotion::class,
                ['getId', 'setId', 'getSex', 'setSex', 'getArg1', 'setArg1', 'getArg2', 'setArg2'],
            ],
        ];
    }

    /**
     * @dataProvider  genProvider
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
