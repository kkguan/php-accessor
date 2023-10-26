<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Attribute\Map;

class PrefixConvention
{
    /**
     * Getter: use 'get'.
     * Setter: use 'set'.
     */
    public const GET_SET = 1;

    /**
     * Getter: when property is boolean, use 'is', otherwise use `get`.
     * Setter: use 'set'.
     */
    public const BOOLEAN_IS = 2;
}
