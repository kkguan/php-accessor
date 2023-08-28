<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method\Generator;

use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpAccessor\Processor\Method\FieldMetadata;

interface GeneratorInterface
{
    public function generate(FieldMetadata $fieldMetadata, AccessorMethodInterface $accessorMethod): void;
}
