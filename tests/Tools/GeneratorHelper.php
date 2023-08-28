<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Test\Tools;

use ArrayIterator;
use PhpAccessor\Console\ConfigurationResolver;
use PhpAccessor\Runner;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;

class GeneratorHelper
{
    public static function genFromClass(string $classname): array
    {
        $ref = new ReflectionClass($classname);
        $path = $ref->getFileName();
        $resolver = new ConfigurationResolver(
            [
                'path' => [$path],
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

        return $runner->getGeneratedFiles();
    }

    /**
     * @return array<array{name:string,body:string,comment:string}>
     */
    public static function getMethods(string $classPath): array
    {
        $source = file_get_contents($classPath);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($source);
        $traverser = new NodeTraverser();
        $visitor = new class() extends NodeVisitorAbstract {
            public array $methods = [];

            private Standard $standard;

            public function __construct()
            {
                $this->standard = new Standard();
            }

            public function enterNode(Node $node): void
            {
                if (! $node instanceof Node\Stmt\ClassMethod) {
                    return;
                }

                $this->methods[$node->name->name] = [
                    'name' => $node->name->name,
                    'body' => $this->standard->prettyPrint($node->getStmts()),
                    'comment' => $node->getDocComment()?->getText(),
                ];
            }
        };
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        return $visitor->methods;
    }
}
