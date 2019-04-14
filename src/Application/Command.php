<?php

namespace DKoehn\DSpec\Application;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Finder\Finder;

use DKoehn\DSpec\Cache\DependencyCache;
use DKoehn\DSpec\Parser\CachedParser;
use DKoehn\DSpec\Watcher\FileWatcher;
use DKoehn\DSpec\TestRunner\TestRunner;

class Command extends BaseCommand
{
    protected $configuration;

    public function __construct(Configuration $configuration, InputDefinition $inputDefinition)
    {
        parent::__construct('dspec');

        $this->configuration = $configuration;
        $this->setDefinition($inputDefinition);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = $this->configuration->getCwd();

        $paths = [];
        if (file_exists($cwd . '/src')) {
            $paths[] = $cwd . '/src';
        }
        if (file_exists($cwd . '/spec')) {
            $paths[] = $cwd . '/spec';
        }

        if (count($paths) === 0) {
            $output->writeln('src/ and spec/ folders not found');
            exit(1);
        }

        $startTime = microtime(true);

        $cacheFile = $cwd . '/.dspec_cache';

        if (file_exists($cacheFile)) {
            $output->writeln('Loading from cache file...');

            /** @var DependencyCache $cache */
            $cache = @unserialize(file_get_contents($cacheFile));
        }

        if (!isset($cache)) {
            $output->writeln('Initializing dependencies...');
            $cache = new DependencyCache;
            $lastModifiedDate = null;
        } else {
            $lastModifiedDate = new \DateTime();
            $lastModifiedDate->setTimestamp($cache->lastBuilt);
        }

        $finder = new Finder();
        $finder->directories([$cwd])
            ->name('*.php');
        if ($lastModifiedDate !== null) {
            $finder->date('since ' . $lastModifiedDate->format('Y-m-d H:i:s'));
        }

        $filePaths = [];
        foreach ($finder->files()->in($paths) as $file) {
            /** @var \SplFileInfo $file */
            $filePaths[] = str_replace($cwd . '/', '', $file->getPath()) . '/' . $file->getFilename();
        }

        $cachedParser = new CachedParser;
        if (count($filePaths)) {
            $cachedParser->parse($cache, $filePaths);
            file_put_contents($cacheFile, serialize($cache));
        }

        // TODO: Handle removed files

        $output->writeln('Cache update took: ' . (microtime(true) - $startTime));

        $testRunner = new TestRunner(
            $this->configuration->getRegexForTestFiles(),
            $this->configuration->getDSpecPath(),
            $cache,
            $output
        );

        $testRunner->runTestsForGitUnstaged();

        $watcher = new FileWatcher(
            $cache,
            $cachedParser,
            $testRunner,
            $output,
            $cacheFile
        );
        $watcher->watch($paths);

        return 0;
    }

    public function getSynopsis($short = false)
    {
        return $this->getName() . ' [options] [files]';
    }
}
