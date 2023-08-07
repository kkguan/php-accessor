<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

abstract class AttributeParameterBuilder
{
    protected $parameterValue;

    public function prepare(Expr $value): static
    {
        $this->parameterValue = null;

        if ($value instanceof ClassConstFetch) {
            if ($value->name instanceof Identifier) {
                $this->parameterValue = $value->name->name;
            }
        }

        return $this;
    }

    abstract public function getName(): string;

    abstract public function build();
}
