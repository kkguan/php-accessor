<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\Overlook;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class OverlookTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                Overlook::class,
                ['getId', 'setId'],
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
        $classMethods = GeneratorHelper::getClassMethods($proxyFile);
        $this->assertCount(count($methods), $classMethods);
        foreach ($methods as $method) {
            $this->assertTrue(in_array($method, $classMethods));
        }
    }
}
