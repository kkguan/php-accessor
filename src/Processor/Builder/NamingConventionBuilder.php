<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Builder;

use PhpAccessor\Attribute\Map\NamingConvention as NamingConventionMap;
use PhpAccessor\Processor\Attribute\NamingConvention;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

class NamingConventionBuilder
{
    private ClassConstFetch $classConstFetch;

    public function setClassConstFetch(ClassConstFetch $classConstFetch): self
    {
        $this->classConstFetch = $classConstFetch;

        return $this;
    }

    public function build(): NamingConvention
    {
        $namingConvention = new NamingConvention();
        if (empty($this->classConstFetch) ||
            !$this->classConstFetch->name instanceof Identifier) {
            return $namingConvention;
        }

        switch ($this->classConstFetch->name->name) {
            case 'LOWER_CAMEL_CASE':
                $namingConvention->setValue(NamingConventionMap::LOWER_CAMEL_CASE);
                break;
            case 'UPPER_CAMEL_CASE':
                $namingConvention->setValue(NamingConventionMap::UPPER_CAMEL_CASE);
                break;
            default:
                break;
        }

        return $namingConvention;
    }
}
