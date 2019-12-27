<?php

namespace CCB\DSpec\Application;

use Symfony\Component\Console\Input\InputDefinition as BaseInputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InputDefinition extends BaseInputDefinition
{
    public function __construct()
    {
        parent::__construct();

        $this->option('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.');
        $this->option('--testPathPattern', null, InputOption::VALUE_REQUIRED, 'A regexp pattern string that is matched against all test paths before executing the test.');
        $this->option('--findRelatedTests', null, InputOption::VALUE_NONE, 'Find and run the tests that cover a space separated list of source files.');
        $this->argument('testFiles', InputArgument::IS_ARRAY);

        $this->option('dspecPath', null, InputOption::VALUE_REQUIRED);
        $this->option('dspec-path', null, InputOption::VALUE_REQUIRED);
    }

    public function argument($name, $mode = null, $description = '', $default = null)
    {
        $argument = new InputArgument($name, $mode, $description, $default);
        $this->addArgument($argument);
        return $this;
    }

    public function option($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $option = new InputOption($name, $shortcut, $mode, $description, $default);
        $this->addOption($option);
        return $this;
    }
}
