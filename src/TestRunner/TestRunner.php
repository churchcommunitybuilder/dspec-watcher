<?php

namespace DKoehn\DSpec\TestRunner;

use Symfony\Component\Console\Output\OutputInterface;

use DKoehn\DSpec\Parser\Adt;

class TestRunner
{
    /** @var string */
    protected $regexForTests;
    /** @var string */
    protected $dspecPath;
    /** @var OutputInterface */
    protected $output;

    public function __construct(
        string $regexForTests,
        string $dspecPath,
        OutputInterface $output
    ) {
        $this->regexForTests = $regexForTests;
        $this->dspecPath = $dspecPath;
        $this->output = $output;
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

        if (count($tests)) {
            $this->output->write("\033\143");
            $this->output->writeln('Running tests...');

            foreach ($tests as $test) {
                $cmd = $this->dspecPath . ' ' . $test;
                $this->output->writeln($cmd);

                exec($cmd, $output, $ret);

                // TODO: Add color
                $this->output->write($output);
            }
        }
    }
}
