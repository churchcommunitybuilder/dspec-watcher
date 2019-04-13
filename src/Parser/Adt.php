<?php

namespace DKoehn\DSpec\Parser;

class Adt
{
    protected $filepath;
    protected $name = null;
    protected $namespace = null;
    protected $dependencies = [];
    protected $dependencyFilePaths = [];

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
        $this->dependencies[$dependency] = true;
    }

    public function getFullyQualifiedName(): string
    {
        return ltrim($this->getNamespace() . '\\' . $this->getName());
    }

    public function getDependencies(): array
    {
        return array_keys($this->dependencies);
    }

    public function hasDependency(Adt $adt)
    {
        $fqn = $adt->getFullyQualifiedName();

        return isset($this->dependencies[$fqn]);
    }

    public function getDependencyFilePaths()
    {
        return array_keys($this->dependencyFilePaths);
    }

    public function hasDependencyByFilePath($filePath)
    {
        return isset($this->dependencyFilePaths[$filePath]);
    }
}
