<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Console\Command;

use ArrayIterator;
use PhpAccessor\Console\ConfigurationResolver;
use PhpAccessor\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    protected function configure(): void
    {
        $this
            ->setDefinition(
                [
                    new InputArgument('path', InputArgument::IS_ARRAY, 'The path.'),
                    new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory to the generated file.'),
                    new InputOption('gen-meta', '', InputOption::VALUE_REQUIRED, 'The metadata should be generated (can be yes or no).'),
                    new InputOption('gen-proxy', '', InputOption::VALUE_REQUIRED, 'The proxy class should be generated (can be yes or no).'),
                ]
            )
            ->setDescription('Fixes a directory or a file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $resolver = new ConfigurationResolver(
            [
                'path' => $path,
                'dir' => $input->getOption('dir'),
                'gen-meta' => $input->getOption('gen-meta'),
                'gen-proxy' => $input->getOption('gen-proxy'),
            ],
            getcwd()
        );

        if (! $resolver->getGenProxy() && ! $resolver->getGenMeta()) {
            $io->error('Both metadata and proxy are set to false');

            return Command::FAILURE;
        }

        $finder = $resolver->getFinder();
        $finder = new ArrayIterator(iterator_to_array($finder));
        $runner = new Runner(
            finder: $finder,
            dir: $resolver->getDir(),
            genMeta: $resolver->getGenMeta(),
            genProxy: $resolver->getGenProxy(),
        );
        $runner->generate();
        foreach ($runner->getGeneratedFiles() as $proxyFile) {
            $io->writeln('[generated-file] ' . $proxyFile);
        }

        return Command::SUCCESS;
    }
}
