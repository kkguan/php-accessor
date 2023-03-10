<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Attribute;

class Data
{
    private NamingConvention $namingConvention;

    public function setNamingConvention(NamingConvention $namingConvention): self
    {
        $this->namingConvention = $namingConvention;

        return $this;
    }

    public function getNamingConvention(): NamingConvention
    {
        return $this->namingConvention;
    }
}
