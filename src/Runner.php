<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor;

use PhpAccessor\File\File;
use PhpAccessor\Meta\ClassMetadata;
use PhpAccessor\Processor\ClassProcessor;
use PhpAccessor\Processor\CommentProcessor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;
use Traversable;

class Runner
{
    private array $generatedFiles = [];

    private File $file;

    public function __construct(
        private Traversable $finder,
        string $dir,
        private bool $genMeta,
        private bool $genProxy,
    ) {
        $this->file = new File($dir);
    }

    public function getGeneratedFiles(): array
    {
        return $this->generatedFiles;
    }

    public function generate(): void
    {
        if (! $this->genProxy && ! $this->genMeta) {
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
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);
        $stmts = $traverser->traverse($stmts);

        $traverser = new NodeTraverser();
        $classProcessor = new ClassProcessor($this->genProxy, new CommentProcessor($nameResolver->getNameContext()));
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
        if (! $this->genProxy) {
            return;
        }

        $this->generatedFiles[] = $this->file->dumpFile(
            File::PROXY,
            $this->getFileName($classProcessor->getClassname()) . '.php',
            $this->getPrintFileContent($stmts)
        );
        $this->generatedFiles[] = $this->file->dumpFile(
            File::ACCESSOR,
            $this->getFileName($classProcessor->getTraitAccessor()->getClassname()) . '.php',
            $this->getPrintFileContent([$classProcessor->getTraitAccessor()->buildTrait()])
        );
    }

    private function generateMetadata(ClassProcessor $classProcessor): void
    {
        if (! $this->genMeta || empty($accessorMethods = $classProcessor->getAccessorMethods())) {
            return;
        }

        $classMetadata = new ClassMetadata('', $classProcessor->getClassname(), $classProcessor->getTraitAccessor()->getClassName());
        array_walk($accessorMethods, fn ($method) => $classMetadata->addMethod($method));
        $this->generatedFiles[] = $this->file->dumpFile(
            File::META,
            $this->getFileName($classProcessor->getClassname()) . '.json',
            json_encode($classMetadata)
        );
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
