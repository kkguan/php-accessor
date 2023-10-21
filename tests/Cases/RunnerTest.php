<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Cases;

use ArrayIterator;
use PhpAccessor\Console\ConfigurationResolver;
use PhpAccessor\Runner;
use PhpAccessor\Test\Mock\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RunnerTest extends TestCase
{
    public function testGenerate()
    {
        $classPath = __ROOT__ . '/tests/Mock/Foo.php';
        $resolver = new ConfigurationResolver(
            [
                'path' => [$classPath],
                'dir' => __ROOT__ . DIRECTORY_SEPARATOR . '.php-accessor',
                'gen-meta' => 'yes',
                'gen-proxy' => 'yes',
            ],
            getcwd()
        );
        $finder = $resolver->getFinder();
        $finder = new ArrayIterator(iterator_to_array($finder));
        $runner = new Runner(
            finder: $finder,
            dir: $resolver->getDir(),
            genMeta: $resolver->getGenMeta(),
            genProxy: $resolver->getGenProxy(),
        );
        $runner->generate();
        $files = $runner->getGeneratedFiles();

        $this->assertCount(3, $files);
        $proxy = $files[0];
        include_once $proxy;
        $foo = new Foo();
        $this->assertIsObject($foo);
    }
}
