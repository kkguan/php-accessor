<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\DefaultNullAll;
use PhpAccessor\Test\Mock\DefaultNullPartial;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DefaultNullTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                DefaultNullAll::class,
                ['getId' => 'return $this->id ?? null;', 'getSex' => 'return $this->sex ?? null;'],
            ],
            [
                DefaultNullPartial::class,
                ['getId' => 'return $this->id;', 'getSex' => 'return $this->sex ?? null;'],
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
        $methodInfo = GeneratorHelper::getMethods($proxyFile);
        foreach ($methods as $method => $body) {
            $this->assertArrayHasKey($method, $methodInfo);
            $this->assertSame($body, $methodInfo[$method]['body']);
        }
    }
}
