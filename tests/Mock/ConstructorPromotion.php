<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Mock;

use PhpAccessor\Attribute\Data;

#[Data]
class ConstructorPromotion
{
    /** @var int */
    private $id;

    public function __construct(
        private $sex,
        protected string $arg1,
        public array $arg2,
        int $arg3
    ) {
    }
}
