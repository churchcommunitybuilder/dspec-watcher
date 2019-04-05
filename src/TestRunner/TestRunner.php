<?php

namespace DKoehn\DSpec\TestRunner;

use Symfony\Component\Console\Output\OutputInterface;

use DKoehn\DSpec\Parser\Adt;
use DKoehn\DSpec\Cache\DependencyCache;

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
        exec('git ls-files --other --modified --exclude-standard', $files, $ret);

        if ($ret !== 0) {
            $this->output->writeln($files[0]);
            exit(1);
        }

        $allRefs = [];
        foreach ($files as $filePath) {
            $allRefs = array_merge($allRefs, $this->getRefsForFile($filePath));
        }

        $refs = [];
        foreach ($allRefs as $ref) {
            /** @var Adt $ref */
            $refs[$ref->getFullyQualifiedName()] = $ref;
        }

        $this->runTestsForRefs(array_values($refs));
    }

    protected function getRefsForFile($filePath)
    {
        $refs = $this->cache->getReferencesForFile($filePath);
        $allRefs = $refs;
        foreach ($refs as $ref) {
            /** @var Adt $ref */
            $allRefs = array_merge(
                $allRefs,
                $this->cache->getReferencesByFQN($ref->getFullyQualifiedName())
            );
        }

        $reducer = function($refs, Adt $adt) use (&$reducer) {
            $newRefs = $this->cache->getReferencesByFQN($adt->getFullyQualifiedName());

            foreach ($newRefs as $ref) {
                if (!in_array($ref, $refs)) {
                    $refs[] = $ref;
                    $refs = array_reduce($refs, $reducer, $refs);
                }
            }

            return $refs;
        };

        return array_reduce($refs, $reducer, $refs);
    }

    public function runTestsForRefs(array $refs)
    {
        $tests = [];
        foreach ($refs as $ref) {
            /** @var Adt $ref */
            if (preg_match("#{$this->regexForTests}#", $ref->getFilePath())) {
                $tests[] = $ref->getFilePath();
            }
        }
        $tests = array_unique($tests);

        if (count($tests)) {
            $this->output->write("\033\143");
            $this->output->writeln('Running tests...');

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
        }
    }
}
