<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Test\Console;

use PhpAccessor\Console\Application;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Input\ArrayInput;

use const DIRECTORY_SEPARATOR;

class ApplicationTest extends TestCase
{
    public function testRun()
    {
        $ref = new ReflectionClass(\PhpAccessor\Test\Mock\Foo::class);
        $ref2 = new ReflectionClass(\PhpAccessor\Test\Mock\SuperFoo::class);
        $ref3 = new ReflectionClass(\PhpAccessor\Test\Mock\GenerateMethodComment::class);
        $input = new ArrayInput([
            'command' => 'generate',
            'path' => [
                $ref->getFileName(),
                $ref2->getFileName(),
                $ref3->getFileName(),
            ],
            '--gen-meta' => 'yes',
            '--dir' => __ROOT__ . DIRECTORY_SEPARATOR . '.php-accessor',
        ]);
        $app = new Application();
        $app->run($input);
    }
}
