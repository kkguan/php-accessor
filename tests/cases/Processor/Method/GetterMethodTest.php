<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Processor\Method;

use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Test\Mock\GenerateMethodComment;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;

class GetterMethodTest extends TestCase
{
    private Lexer $phpDocLexer;
    private PhpDocParser $phpDocParser;

    public function __construct(string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->phpDocLexer = new Lexer();
        $constantExpressionParser = new ConstExprParser();
        $this->phpDocParser = new PhpDocParser(new TypeParser($constantExpressionParser), $constantExpressionParser);
    }

    /**
     * @dataProvider  getGenerateMethodCommentExamples
     */
    public function testGenerateMethodComment($className, $fieldName, $fieldTypes, $propertyComment, $methodComment)
    {
        $ast = null;
        if (!empty($propertyComment)) {
            $tokens = new TokenIterator($this->phpDocLexer->tokenize($propertyComment));
            $ast = $this->phpDocParser->parse($tokens);
        }

        $getterMethod = new GetterMethod($className, $fieldName, $fieldTypes, $ast);
        $getterMethod->generateMethodComment();
        $this->assertSame($methodComment, $getterMethod->getMethodComment());
    }

    public function getGenerateMethodCommentExamples(): array
    {
        return [
            [GenerateMethodComment::class, 'id', [], "/**\n * @var int\n */", "/**\n * @return int\n */"],
            [GenerateMethodComment::class, 'name', ['string'], '', ''],
            [GenerateMethodComment::class, 'age', ['int'], '', ''],
            [GenerateMethodComment::class, 'array1', [], "/**\n * @var \PhpAccessor\Test\Mock\FooSub[]\n */", "/**\n * @return \PhpAccessor\Test\Mock\FooSub[]\n */"],
            [GenerateMethodComment::class, 'array2', ['array'], "/**\n * @var \PhpAccessor\Test\Mock\FooSub[]\n */", "/**\n * @return \PhpAccessor\Test\Mock\FooSub[]\n */"],
            [GenerateMethodComment::class, 'array3', [], "/**\n * @var string[]\n */", "/**\n * @return string[]\n */"],
        ];
    }
}
