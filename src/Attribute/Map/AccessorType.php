<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Attribute\Map;

/**
 * 要生成的访问器类型.
 */
class AccessorType
{
    public const BOTH = 'both';

    public const GETTER = 'getter';

    public const SETTER = 'setter';
}
