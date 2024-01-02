<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\File;

use ArrayIterator;
use PhpAccessor\Exception\ProxyGenerationException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use const DIRECTORY_SEPARATOR;

class File
{
    public const META = 'meta';

    public const PROXY = 'proxy';

    public const ACCESSOR = 'accessor';

    private ArrayIterator $dirs;

    private Filesystem $filesystem;

    public function __construct(private string $workDir)
    {
        $this->filesystem = new Filesystem();
        $this->initDirectories();
    }

    public function dumpFile($dirType, string $filename, $content): string
    {
        $dir = $this->dirs->offsetGet($dirType);
        if (empty($dir)) {
            throw new ProxyGenerationException('Illegal directory type: ' . $dirType);
        }

        $filePath = $dir . $filename;
        $this->filesystem->dumpFile($filePath, $content);

        return $filePath;
    }

    private function initDirectories(): void
    {
        try {
            $this->dirs = new ArrayIterator([
                static::META => $this->workDir . DIRECTORY_SEPARATOR . static::META . DIRECTORY_SEPARATOR,
                static::PROXY => $this->workDir . DIRECTORY_SEPARATOR . static::PROXY . DIRECTORY_SEPARATOR,
                static::ACCESSOR => $this->workDir . DIRECTORY_SEPARATOR . static::PROXY . DIRECTORY_SEPARATOR . static::ACCESSOR . DIRECTORY_SEPARATOR,
            ]);
            $this->filesystem->mkdir($this->dirs);
        } catch (IOException $e) {
            if (str_ends_with($e->getMessage(), 'File exists')) {
                return;
            }

            throw $e;
        }
    }
}
