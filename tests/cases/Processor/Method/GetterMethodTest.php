<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Processor\Method;

use PhpAccessor\Processor\Method\GetterMethod;
use PhpAccessor\Test\Mock\GenerateMethodComment;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\TestCase;

class GetterMethodTest extends TestCase
{
    /**
     * @dataProvider  getGenerateMethodCommentExamples
     */
    public function testGenerateMethodComment(string $fieldName, array $fieldTypes, ?PhpDocNode $propertyComment, string $methodComment)
    {
        $getterMethod = new GetterMethod(GenerateMethodComment::class, $fieldName, $fieldTypes, $propertyComment);
        $getterMethod->generateMethodComment();
        $this->assertSame($methodComment, $getterMethod->getMethodComment());
    }

    public function getGenerateMethodCommentExamples(): array
    {
        return [
            ['name', ['string'], null, ''],
            ['age', ['int'], null, ''],
            [
                'id', [],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new IdentifierTypeNode('int'),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return int\n */",
            ],
            [
                'array1', [],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new ArrayTypeNode(
                                new IdentifierTypeNode('\PhpAccessor\Test\Mock\FooSub')
                            ),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return \PhpAccessor\Test\Mock\FooSub[]\n */",
            ],
            [
                'array2', ['array'],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new ArrayTypeNode(
                                new IdentifierTypeNode('\PhpAccessor\Test\Mock\FooSub')
                            ),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return \PhpAccessor\Test\Mock\FooSub[]\n */",
            ],
            [
                'array3', ['array'],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new ArrayTypeNode(
                                new IdentifierTypeNode('string')
                            ),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return string[]\n */",
            ],
            [
                'array4', ['array'],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new GenericTypeNode(
                                new IdentifierTypeNode('array'),
                                [new IdentifierTypeNode('string')]
                            ),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return array<string>\n */",
            ],
            [
                'array5', ['array'],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new GenericTypeNode(
                                new IdentifierTypeNode('array'),
                                [new IdentifierTypeNode('\PhpAccessor\Test\Mock\FooSub')]
                            ),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return array<\PhpAccessor\Test\Mock\FooSub>\n */",
            ],
            [
                'array6', ['array'],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new GenericTypeNode(
                                new IdentifierTypeNode('array'),
                                [
                                    new IdentifierTypeNode('string'),
                                    new IdentifierTypeNode('\PhpAccessor\Test\Mock\FooSub'),
                                ]
                            ),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return array<string, \PhpAccessor\Test\Mock\FooSub>\n */",
            ],
            [
                'foo', [],
                new PhpDocNode([
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new IdentifierTypeNode('int'),
                            '',
                            ''
                        )
                    ),
                    new PhpDocTagNode(
                        '@var',
                        new VarTagValueNode(
                            new IdentifierTypeNode('string'),
                            '',
                            ''
                        )
                    ),
                ]),
                "/**\n * @return int\n */",
            ],
        ];
    }
}
