<?php

namespace DKoehn\DSpec\TestRunner;

use Symfony\Component\Console\Output\OutputInterface;

use DKoehn\DSpec\Cache\DependencyCache;
use DKoehn\DSpec\DependencyResolver\DependencyResolver;

class TestRunner
{
    /** @var string */
    protected $regexForTests;
    /** @var string */
    protected $dspecPath;
    /** @var DependencyCache */
    protected $cache;
    /** @var OutputInterface */
    protected $output;

    public function __construct(
        string $regexForTests,
        string $dspecPath,
        DependencyCache $cache,
        OutputInterface $output
    ) {
        $this->regexForTests = $regexForTests;
        $this->dspecPath = $dspecPath;
        $this->cache = $cache;
        $this->output = $output;
    }

    public function runTestsForGitUnstaged()
    {
        $this->output->write("\033\143");
        $this->output->writeln('Determining tests to run...');

        exec('git ls-files --other --modified --exclude-standard', $changedFiles, $ret);

        if ($ret !== 0) {
            $this->output->writeln($changedFiles[0]);
            exit(1);
        }

        $changedFiles = array_filter($changedFiles, function($file) {
            return !!preg_match('#\.php$#', $file);
        });

        $tests = $this->findRelatedTests($changedFiles);

        if (count($tests) === 0) {
           $this->output->write("\033\143");
            $this->output->writeln('Watching files for changes...');
        } else {
            $this->runTests($tests);
        }
    }

    protected function findRelatedTests(array $changedFilePaths)
    {
        $dependencyResolver = new DependencyResolver($this->cache);

        return $dependencyResolver->resolveInverse($changedFilePaths, function($filePath) {
            return $this->isTestFilePath($filePath);
        });
    }

    protected function isTestFilePath($filePath)
    {
        return !!preg_match("#{$this->regexForTests}#", $filePath);
    }

    public function runTests(array $tests)
    {
        $tests = array_unique($tests);

        if (count($tests) > 0) {
            $this->output->writeln('Running tests...');

            $start = microtime(true);

            foreach ($tests as $test) {
                $cmd = $this->dspecPath . ' ' . $test;
                $this->output->write($test);

                exec($cmd, $output, $ret);

                if ($ret === 0) {
                    $this->output->writeln(' ✔');
                } else {
                    // TODO: Add color
                    $this->output->writeln(' ✖');
                    foreach ($output as $line) {
                        $this->output->writeln($line);
                    }
                }
            }

            $this->output->writeln('Tests finished in: ' . round(microtime(true) - $start, 3));
        }
    }
}
