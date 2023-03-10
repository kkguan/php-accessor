<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Attribute;

use Attribute;
use PhpAccessor\Attribute\Map\NamingConvention;

#[Attribute(Attribute::TARGET_CLASS)]
class Data
{
    /**
     * @see NamingConvention
     */
    private int $namingConvention;

    public function __construct(int $namingConvention = NamingConvention::NONE)
    {
        $this->namingConvention = $namingConvention;
    }
}
