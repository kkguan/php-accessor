<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Test\Mock\GetterMethodComment;
use PhpAccessor\Test\Tools\GeneratorHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GetterMethodCommentTest extends TestCase
{
    public function genProvider(): array
    {
        return [
            [
                GetterMethodComment::class,
                [
                    'getName' => null,
                    'getAge' => null,
                    'getId' => "/**\n     * @return int\n     */",
                    'getArray1' => "/**\n     * @return \\PhpAccessor\\Test\\Mock\\FooSub[]\n     */",
                    'getArray2' => "/**\n     * @return \\PhpAccessor\\Test\\Mock\\FooSub[]\n     */",
                    'getArray3' => "/**\n     * @return string[]\n     */",
                    'getArray4' => "/**\n     * @return array<string>\n     */",
                    'getArray5' => "/**\n     * @return array<\\PhpAccessor\\Test\\Mock\\FooSub>\n     */",
                    'getArray6' => "/**\n     * @return array<string, \\PhpAccessor\\Test\\Mock\\FooSub>\n     */",
                    'getFoo' => "/**\n     * @return int\n     */",
                ],
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

        foreach ($methods as $method => $comment) {
            $this->assertArrayHasKey($method, $methodInfo);
            $this->assertSame($comment, $methodInfo[$method]['comment']);
        }
    }
}
