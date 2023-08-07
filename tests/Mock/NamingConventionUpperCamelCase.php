<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;
use PhpAccessor\Attribute\Map\NamingConvention;

#[Data(namingConvention: NamingConvention::UPPER_CAMEL_CASE)]
class NamingConventionUpperCamelCase
{
    private $naming_convention;
}
