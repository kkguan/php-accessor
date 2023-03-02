<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Console;

use PhpAccessor\Console\Command\GenerateCommand;
use Symfony\Component\Console\Application  as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('PHP Accessor');
        $this->add(new GenerateCommand());
    }
}
