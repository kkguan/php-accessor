<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method\Generator\Getter;

use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadata;
use PhpAccessor\Processor\Method\Generator\GeneratorInterface;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;

class MethodCommentGenerator implements GeneratorInterface
{
    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void
    {
        $comment = $fieldMetadata->getComment();
        if (empty($comment) || empty($varTagValues = $comment->getVarTagValues())) {
            return;
        }

        $accessorMethod->setMethodComment((string) new PhpDocNode([
            new PhpDocTagNode('@return', new ReturnTagValueNode($varTagValues[0]->type, '')),
        ]));
    }
}
