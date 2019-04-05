<?php

namespace DKoehn\DSpec\TestRunner;

use DKoehn\DSpec\Parser\Adt;

class TestRunner
{
    protected $regexForTests;
    protected $dspecPath;

    public function __construct(string $regexForTests, string $dspecPath)
    {
        $this->regexForTests = $regexForTests;
        $this->dspecPath = $dspecPath;
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
            echo shell_exec($this->dspecPath . ' -f progress ' . implode(' ', $tests));
        }
    }
}
