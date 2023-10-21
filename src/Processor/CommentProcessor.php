<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpParser\Comment\Doc;
use PhpParser\NameContext;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

class CommentProcessor
{
    private const PRIMITIVE_TYPES = [
        'null' => 'null',
        'bool' => 'bool',
        'boolean' => 'bool',
        'string' => 'string',
        'int' => 'int',
        'integer' => 'int',
        'float' => 'float',
        'double' => 'float',
        'array' => 'array',
        'object' => 'object',
        'callable' => 'callable',
        'resource' => 'resource',
        'mixed' => 'mixed',
        'iterable' => 'iterable',
    ];

    private Lexer $phpDocLexer;

    private PhpDocParser $phpDocParser;

    /** @var PhpDocNode[] */
    private array $docNodes = [];

    public function __construct(
        private NameContext $nameContext,
    ) {
        $this->phpDocLexer = new Lexer();
        $constantExpressionParser = new ConstExprParser();
        $this->phpDocParser = new PhpDocParser(new TypeParser($constantExpressionParser), $constantExpressionParser);
    }

    public function buildDocNode(Node $node): ?PhpDocNode
    {
        if (isset($this->docNodes[spl_object_id($node)])) {
            return $this->docNodes[spl_object_id($node)];
        }

        if (empty($docComment = $node->getDocComment())) {
            return null;
        }

        $tokens = new TokenIterator($this->phpDocLexer->tokenize($docComment->getText()));
        $ast = $this->phpDocParser->parse($tokens);
        foreach ($ast->getVarTagValues() as $varTagValueNode) {
            $this->resolveTypeNode($varTagValueNode->type);
        }

        $node->setDocComment(new Doc((string) $ast));
        $this->docNodes[spl_object_id($node)] = $ast;

        return $ast;
    }

    protected function resolveTypeNode(TypeNode $typeNode): void
    {
        if ($typeNode instanceof IdentifierTypeNode) {
            $typeNode->name = $this->resolveTypeName($typeNode);

            return;
        }

        if ($typeNode instanceof ArrayTypeNode) {
            $this->resolveTypeNode($typeNode->type);
        } elseif ($typeNode instanceof GenericTypeNode) {
            foreach ($typeNode->genericTypes as $genericType) {
                $this->resolveTypeNode($genericType);
            }
        } elseif ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $type) {
                $this->resolveTypeNode($type);
            }
        } elseif ($typeNode instanceof ArrayShapeNode) {
            foreach ($typeNode->items as $item) {
                $this->resolveTypeNode($item->valueType);
            }
        }
    }

    protected function resolveTypeName(IdentifierTypeNode $node): string
    {
        if (isset(self::PRIMITIVE_TYPES[$node->name])) {
            return $node->name;
        }

        $resolvedName = $this->nameContext->getResolvedName(new Node\Name($node->name), Node\Stmt\Use_::TYPE_NORMAL);
        if (empty($resolvedName)) {
            return $node->name;
        }

        return $resolvedName->toCodeString();
    }
}
