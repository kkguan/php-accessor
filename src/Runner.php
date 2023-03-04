<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor;

use ArrayIterator;
use PhpAccessor\Meta\ClassMetadata;
use PhpAccessor\Processor\ClassProcessor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Traversable;

use const DIRECTORY_SEPARATOR;

class Runner
{
    private ArrayIterator $dirs;

    private array $generatedFiles = [];

    private Filesystem $filesystem;

    public function __construct(
        private Traversable $finder,
        string $dir,
        private bool $genMeta,
        private bool $genProxy,
    ) {
        $this->filesystem = new Filesystem();
        $this->mkdir($dir);
    }

    private function mkdir($dir): void
    {
        $this->dirs = new ArrayIterator([
            'meta' => $dir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR,
            'proxy' => $dir . DIRECTORY_SEPARATOR . 'proxy' . DIRECTORY_SEPARATOR,
            'accessor' => $dir . DIRECTORY_SEPARATOR . 'proxy' . DIRECTORY_SEPARATOR . 'accessor' . DIRECTORY_SEPARATOR,
        ]);
        $this->filesystem->mkdir($this->dirs);
    }

    public function getGeneratedFiles(): array
    {
        return $this->generatedFiles;
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

        $classProcessor->isGenCompleted() && $this->generateProxy($classProcessor, $ast);
        $this->generateMetadata($classProcessor);
    }

    /**
     * @param Node[] $stmts
     */
    private function generateProxy(ClassProcessor $classProcessor, array $stmts): void
    {
        if (!$this->genProxy) {
            return;
        }

        $proxyFilePath = $this->dirs->offsetGet('proxy') . $this->getFileName($classProcessor->getClassname()) . '.php';
        $this->filesystem->dumpFile($proxyFilePath, $this->getPrintFileContent($stmts));
        $this->generatedFiles[] = $proxyFilePath;
        $accessorFilePath = $this->dirs->offsetGet('accessor') . $this->getFileName($classProcessor->getTraitAccessor()->getClassname()) . '.php';
        $this->filesystem->dumpFile($accessorFilePath, $this->getPrintFileContent([$classProcessor->getTraitAccessor()->buildTrait()]));
        $this->generatedFiles[] = $accessorFilePath;
    }

    private function generateMetadata(ClassProcessor $classProcessor): void
    {
        if (!$this->genMeta || empty($classProcessor->getAccessorMethods())) {
            return;
        }

        $classMetadata = new ClassMetadata('', $classProcessor->getClassname(), $classProcessor->getTraitAccessor()->getClassName());
        foreach ($classProcessor->getAccessorMethods() as $accessorMethod) {
            $classMetadata->addMethod($accessorMethod);
        }
        $metaFilePath = $this->dirs->offsetGet('meta') . $this->getFileName($classProcessor->getClassname()) . '.json';
        $this->filesystem->dumpFile($metaFilePath, json_encode($classMetadata));
        $this->generatedFiles[] = $metaFilePath;
    }

    private function getFileName($classname): string
    {
        return str_replace('\\', '@', $classname);
    }

    private function getPrintFileContent(array $stmts): string
    {
        return (new Standard())->prettyPrintFile($stmts);
    }
}
