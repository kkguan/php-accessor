<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor;

use PhpAccessor\Exception\MetadataGenerationException;
use PhpAccessor\Exception\ProxyGenerationException;
use PhpAccessor\Meta\ClassMetadata;
use PhpAccessor\Method\AccessorMethod;
use PhpAccessor\Method\ClassProcessor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;
use Traversable;

use const DIRECTORY_SEPARATOR;

class Runner
{
    public const ACCESSOR_FOLDER = '.php-accessor';

    private array $proxyFiles = [];

    public function __construct(
        private Traversable $finder,
        private string $dir,
        private bool $genMeta,
        private bool $genProxy,
    ) {
        if (!is_dir($dir) && !@mkdir($dir)) {
            throw new ProxyGenerationException(sprintf('Failed to create "%s"', $dir));
        }
    }

    public function getProxyFiles(): array
    {
        return $this->proxyFiles;
    }

    public function generate(): void
    {
        if (!$this->genProxy && !$this->genMeta) {
            return;
        }
        foreach ($this->finder as $value) {
            $this->generateFile($value);
        }
    }

    private function generateFile(SplFileInfo $fileInfo): void
    {
        $source = file_get_contents($fileInfo->getRealPath());
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($source);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());

        $stmts = $traverser->traverse($stmts);
        $traverser = new NodeTraverser();

        $classProcessor = new ClassProcessor($this->genProxy);
        $traverser->addVisitor($classProcessor);
        $ast = $traverser->traverse($stmts);

        $classProcessor->isGenCompleted() && $this->generateProxy($classProcessor->getClassname(), $ast);
        $this->generateMetadata($classProcessor->getClassname(), $classProcessor->getAccessorMethods());
    }

    /**
     * @param Node[] $stmts
     */
    private function generateProxy(string $classname, array $stmts): void
    {
        if (!$this->genProxy) {
            return;
        }

        $dir = $this->dir.DIRECTORY_SEPARATOR.'proxy';
        if (!is_dir($dir) && !@mkdir($dir)) {
            throw new ProxyGenerationException(sprintf('Failed to create "%s"', $dir));
        }

        $prettyPrinter = new Standard();
        $proxyContent = $prettyPrinter->prettyPrintFile($stmts);
        $fileName = str_replace('\\', '@', $classname);
        if (false === @file_put_contents($dir.DIRECTORY_SEPARATOR.$fileName.'.php', $proxyContent)) {
            throw new ProxyGenerationException(sprintf('Failed to write file "%s".', $fileName));
        }

        $this->proxyFiles[] = $dir.DIRECTORY_SEPARATOR.$fileName.'.php';
    }

    /**
     * @param AccessorMethod[] $accessorMethods
     */
    private function generateMetadata(string $classname, array $accessorMethods): void
    {
        if (!$this->genMeta || empty($accessorMethods)) {
            return;
        }

        $dir = $this->dir.DIRECTORY_SEPARATOR.'meta';
        if (!is_dir($dir) && !@mkdir($dir)) {
            throw new MetadataGenerationException(sprintf('Failed to create "%s"', $dir));
        }

        $classMetadata = new ClassMetadata('', $classname);
        foreach ($accessorMethods as $accessorMethod) {
            $classMetadata->addMethod($accessorMethod);
        }
        $fileName = str_replace('\\', '@', $classname);
        $data = json_encode($classMetadata);
        if (false === @file_put_contents($dir.DIRECTORY_SEPARATOR.$fileName.'.json', $data)) {
            throw new MetadataGenerationException(sprintf('Failed to write file "%s".', $fileName));
        }
    }
}
