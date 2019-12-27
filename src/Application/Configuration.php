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

    /** @var bool */
    protected $findRelatedTests = false;

    /** @var string[] */
    protected $relatedTests;

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

    public function shouldFindRelatedTests(): bool
    {
        return $this->findRelatedTests;
    }

    public function getRelatedTests(): array
    {
        return $this->relatedTests;
    }

    public static function load(InputInterface $input, Environment $environment)
    {
        $configuration = new static();

        $hasFindRelatedTests = false;
        global $argv;
        foreach ($argv as $arg) {
            if ($arg === '--findRelatedTests') {
                $hasFindRelatedTests = true;
                break;
            }
        }

        $configuration->regexForTestFiles = $input->getOption('testPathPattern') ?? 'Spec.php$';
        $configuration->findRelatedTests = $hasFindRelatedTests;
        $configuration->relatedTests = $input->getArgument('testFiles');
        $configuration->dspecPath = $input->getOption('dspecPath') ?? $input->getOption('dspec-path') ?? $environment->getDSpecPath();
        $configuration->cwd = $environment->getCwd();

        return $configuration;
    }
}
