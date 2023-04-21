<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\NodeVisitor;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitor\NameResolver as BaseNameResolver;

class NameResolver extends BaseNameResolver
{
    private const PRIMITIVE_TYPES = [
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

    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if (!$node instanceof Property) {
            return null;
        }

        if (empty($docComment = $node->getDocComment())) {
            return null;
        }

        if (!empty($doc = $this->resolveDocComment($docComment))) {
            $node->setDocComment($doc);
        }

        return null;
    }

    private function resolveDocComment(Doc $docComment): ?Doc
    {
        if (empty($docComment)) {
            return null;
        }

        if (!preg_match('/(?<=@var\s)[^\s]+/', $docComment->getText(), $matches)) {
            return null;
        }

        $name = $matches[0];
        if ($isArray = str_ends_with($name, '[]')) {
            $name = substr($name, 0, -2);
        }

        if (isset(self::PRIMITIVE_TYPES[$name])) {
            return null;
        }

        $resolvedName = $this->nameContext->getResolvedName(new Node\Name($name), Node\Stmt\Use_::TYPE_NORMAL);
        if (empty($resolvedName)) {
            return null;
        }

        $name = $resolvedName->toCodeString() . ($isArray ? '[]' : '');
        $text = str_replace($matches[0], $name, $docComment->getText());

        return new Doc($text);
    }
}
