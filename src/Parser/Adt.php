<?php

namespace DKoehn\DSpec\Parser;

class Adt
{
    protected $filepath;
    protected $name = null;
    protected $namespace = null;
    protected $dependicies = [];

    protected $hasBeenUniqued = false;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    public function getFilePath(): string
    {
        return $this->filepath;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function addDependency(string $dependency)
    {
        $this->dependicies[] = $dependency;
        $this->hasBeenUniqued = false;
    }

    public function getFullyQualifiedName(): string
    {
        return ltrim($this->getNamespace() . '\\' . $this->getName());
    }

    public function getDependencies(): array
    {
        if (!$this->hasBeenUniqued) {
            $this->dependicies = array_unique($this->dependicies);
            $this->hasBeenUniqued = true;
        }

        return $this->dependicies;
    }

    public function hasDependency(Adt $adt)
    {
        $fqn = $adt->getFullyQualifiedName();

        foreach ($this->getDependencies() as $dep) {
            if ($fqn === $dep) {
                return true;
            }
        }

        return false;
    }
}
