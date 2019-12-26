<?php

namespace CCB\DSpec\Application;

use Symfony\Component\Console\Input\InputInterface;

class Configuration
{
    /** @var string */
    protected $regexForTestFiles;

    /** @var string */
    protected $dspecPath;

    /** @var string */
    protected $cwd;

    public function getRegexForTestFiles(): ?string
    {
        return $this->regexForTestFiles;
    }

    public function getDSpecPath(): string
    {
        return $this->dspecPath;
    }

    public function getCwd(): string
    {
        return $this->cwd;
    }

    public static function load(InputInterface $input, Environment $environment)
    {
        $configuration = new static();

        $configuration->regexForTestFiles = $input->getArgument('regexForTestFiles') ?? 'Spec.php$';
        $configuration->dspecPath = $input->getOption('dspecPath') ?? $input->getOption('dspec-path') ?? $environment->getDSpecPath();
        $configuration->cwd = $environment->getCwd();

        return $configuration;
    }
}
