<?php

namespace DKoehn\DSpec\Application;

use Symfony\Component\Console\Input\ArgvInput;

class CliArgsParser extends ArgvInput
{
    /** @var string[] */
    protected $options;

    /** @var string[] */
    protected $arguments;

    /**
     * @param string[] $options
     * @param string[] $arguments
     */
    public function __construct(array $options, array $arguments)
    {
        $this->options = $options;
        $this->arguments = $arguments;
    }

    /**
     * @return string[]
     */
    public function parse()
    {
        $input = new ArgvInput($this->arguments);

        $args = [];

        foreach ($this->options as $option) {
            if ($input->hasParameterOption($option)) {
                $name = ltrim($option, '-');
                $args[$name] = $input->getParameterOption($option);
            }
        }

        return $args;
    }
}
