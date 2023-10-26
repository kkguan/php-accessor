<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;
use PhpAccessor\Attribute\Map\AccessorType;
use PhpAccessor\Attribute\Map\NamingConvention;
use PhpAccessor\Attribute\Map\PrefixConvention;

#[Data(namingConvention: NamingConvention::NONE, accessorType: AccessorType::BOTH, prefixConvention: PrefixConvention::BOOLEAN_IS)]
class PrefixConventionBooleanIs
{
    private bool $foo;
}
