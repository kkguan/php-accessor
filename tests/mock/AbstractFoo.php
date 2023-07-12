<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;

#[Data]
abstract class AbstractFoo
{
    private int $id;
}