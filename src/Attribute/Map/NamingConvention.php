<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Attribute\Map;

class NamingConvention
{
    // 首字母大写
    public const NONE = 1;

    // 小驼峰
    public const LOWER_CAMEL_CASE = 2;

    // 大驼峰
    public const UPPER_CAMEL_CASE = 3;
}
