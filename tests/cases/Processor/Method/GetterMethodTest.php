<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Processor\Method;

use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Test\Mock\GenerateMethodComment;
use PHPUnit\Framework\TestCase;

class GetterMethodTest extends TestCase
{
    /**
     * @dataProvider  getGenerateMethodCommentExamples
     */
    public function testGenerateMethodComment($className, $fieldName, $fieldTypes, $comment)
    {
        $getterMethod = new GetterMethod($className, $fieldName, $fieldTypes);
        $getterMethod->generateMethodComment();
        $this->assertSame($comment, $getterMethod->getMethodComment());
    }

    public function getGenerateMethodCommentExamples(): array
    {
        return [
            [GenerateMethodComment::class, 'id', [], "/**\n    * @return int\n    */"],
            [GenerateMethodComment::class, 'name', ['string'], ''],
            [GenerateMethodComment::class, 'age', ['int'], ''],
            [GenerateMethodComment::class, 'array1', [], "/**\n    * @return \PhpAccessor\Test\Mock\FooSub[]\n    */"],
            [GenerateMethodComment::class, 'array2', ['array'], "/**\n    * @return \PhpAccessor\Test\Mock\FooSub[]\n    */"],
        ];
    }
}
