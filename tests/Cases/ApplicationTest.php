<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use PhpAccessor\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @internal
 * @coversNothing
 */
class ApplicationTest extends TestCase
{
    public function testRun()
    {
        $input = new ArrayInput([
            'command' => 'generate',
            'path' => [
                __ROOT__ . '/tests/Mock/Foo.php',
            ],
            '--gen-meta' => 'yes',
            '--dir' => __ROOT__ . DIRECTORY_SEPARATOR . '.php-accessor',
        ]);
        $app = new Application();
        $app->setAutoExit(false);
        $res = $app->run($input);
        $this->assertSame(0, $res);
    }
}
