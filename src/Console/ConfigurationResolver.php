<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Console;

use PhpAccessor\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function array_key_exists;
use function is_string;

use const DIRECTORY_SEPARATOR;

class ConfigurationResolver
{
    private string $cwd;

    private array $options = [
        'path' => [],
        'dir' => null,
        'gen-meta' => null,
        'gen-proxy' => null,
    ];

    private array $path = [];

    private $dir;

    private ?iterable $finder = null;

    private bool $genMeta;

    private bool $genProxy;

    public function __construct(
        array $options,
        string $cwd,
    ) {
        $this->cwd = $cwd;

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getFinder(): iterable
    {
        if ($this->finder === null) {
            $this->finder = $this->resolveFinder();
        }

        return $this->finder;
    }

    public function getPath(): array
    {
        if (empty($this->path)) {
            $this->path = $this->buildAbsolutePaths($this->options['path']);
        }

        return $this->path;
    }

    public function getDir(): string
    {
        if (empty($this->dir)) {
            $cwd = $this->cwd;
            $dir = $this->options['dir'] ?: null;
            if (empty($dir)) {
                $this->dir = $cwd;
            } else {
                $this->dir = $dir;
            }
        }

        return $this->dir;
    }

    public function getGenMeta(): bool
    {
        if (! isset($this->genMeta)) {
            if ($this->options['gen-meta'] === null) {
                $this->genMeta = false;
            } else {
                $this->genMeta = $this->resolveOptionBooleanValue('gen-meta');
            }
        }

        return $this->genMeta;
    }

    public function getGenProxy(): bool
    {
        if (! isset($this->genProxy)) {
            if ($this->options['gen-proxy'] === null) {
                $this->genProxy = true;
            } else {
                $this->genProxy = $this->resolveOptionBooleanValue('gen-proxy');
            }
        }

        return $this->genProxy;
    }

    private function resolveOptionBooleanValue(string $optionName): bool
    {
        $value = $this->options[$optionName];

        if (! is_string($value)) {
            throw new InvalidConfigurationException(sprintf('Expected boolean or string value for option "%s".', $optionName));
        }

        if ($value === 'yes') {
            return true;
        }

        if ($value === 'no') {
            return false;
        }

        throw new InvalidConfigurationException(sprintf('Expected "yes" or "no" for option "%s", got "%s".', $optionName, $value));
    }

    private function buildAbsolutePaths(array $paths): array
    {
        $filesystem = new Filesystem();
        $cwd = $this->cwd;

        return array_map(
            static function (string $rawPath) use ($cwd, $filesystem): string {
                $path = trim($rawPath);

                if ($path === '') {
                    throw new InvalidConfigurationException("Invalid path: \"{$rawPath}\".");
                }

                $absolutePath = $filesystem->isAbsolutePath($path)
                    ? $path
                    : $cwd . DIRECTORY_SEPARATOR . $path;

                if (! file_exists($absolutePath)) {
                    throw new InvalidConfigurationException(sprintf('The path "%s" is not readable.', $path));
                }

                return $absolutePath;
            },
            $paths
        );
    }

    private function setOption(string $name, $value): void
    {
        if (! array_key_exists($name, $this->options)) {
            throw new InvalidConfigurationException(sprintf('Unknown option name: "%s".', $name));
        }

        $this->options[$name] = $value;
    }

    private function resolveFinder(): iterable
    {
        $paths = array_filter(array_map(
            static function (string $path) {
                return realpath($path);
            },
            $this->getPath()
        ));

        $pathsByType = [
            'file' => [],
            'dir' => [],
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $pathsByType['file'][] = $path;
            } else {
                $pathsByType['dir'][] = $path . DIRECTORY_SEPARATOR;
            }
        }

        return (new Finder())
            ->files()
            ->name('*.php')
            ->exclude('vendor')
            ->in($pathsByType['dir'])
            ->append($pathsByType['file']);
    }
}
