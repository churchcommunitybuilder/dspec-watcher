<?php

namespace CCB\DSpec\Application;

class Environment
{
    /** @var InputDefinition */
    protected $inputDefinition;

    /** @var string */
    protected $dspecPath;

    /** @var string */
    protected $cwd;

    public function __construct(
        InputDefinition $inputDefinition,
        string $dspecPath,
        string $cwd
    ) {
        $this->inputDefinition = $inputDefinition;
        $this->dspecPath = $dspecPath;
        $this->cwd = $cwd;
    }

    public function getInputDefinition()
    {
        return $this->inputDefinition;
    }

    public function getDSpecPath(): string
    {
        return $this->dspecPath;
    }

    public function getCwd(): string
    {
        return $this->cwd;
    }
}
