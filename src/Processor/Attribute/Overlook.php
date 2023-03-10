<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Attribute;

class Overlook
{
    private bool $isOverlook = true;

    public function isOverlook(): bool
    {
        return $this->isOverlook;
    }
}
