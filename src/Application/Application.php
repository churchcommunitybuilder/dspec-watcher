<?php

namespace DKoehn\DSpec\Application;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArgvInput;

class Application extends BaseApplication
{
    const VERSION_NUMBER = '0.1.0';

    const NAME = 'DSpec';

    /** @var Environment */
    protected $environment;

    /** @var Configuration */
    protected $configuration;

    public function __construct(Environment $environment)
    {
        parent::__construct(self::NAME, self::VERSION_NUMBER);

        $this->environment = $environment;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($input === null) {
            $input = $this->getInput();
        }

        return parent::run($input, $output);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->configuration = Configuration::load($input, $this->environment);

        $this->add(new Command($this->configuration));

        $exit = parent::doRun($input, $output);

        return $exit;
    }

    public function getCommandName(InputInterface $input)
    {
        return 'dspec';
    }

    public function getInput(array $argv = null)
    {
        try {
            return new ArgvInput($argv, $this->environment->getInputDefinition());
        } catch (\Exception $e) {
            // TODO: Handle exceptions
            exit(1);
        }
    }
}
