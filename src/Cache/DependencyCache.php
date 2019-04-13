<?php

namespace DKoehn\DSpec\Cache;

use DKoehn\DSpec\Parser\Adt;


class DependencyCache
{
    /** @var array */
    protected $adtsByFilePath = [];

    /** @var array */
    protected $adtsByFQN = [];

    public $lastBuilt = null;

    public function setLastBuilt()
    {
        $this->lastBuilt = time();
    }

    public function add(Adt $adt)
    {
        $this->adtsByFilePath[$adt->getFilePath()] = $adt;
        $fqn = $adt->getFullyQualifiedName();
        if ($fqn !== null) {
            $this->adtsByFQN[$fqn] = $adt;
        }
    }

    public function removeByFQN($fqn)
    {
        if (array_key_exists($fqn, $this->adtsByFQN)) {
            /** @var Adt $adt */
            $adt = $this->adtsByFQN[$fqn];
            unset($this->adtsByFQN[$fqn]);
            unset($this->adtsByFilePath[$adt->getFilePath()]);
        }
    }

    public function getAdtByFilePath($filePath): ?Adt
    {
        if ($this->hasFilePath($filePath)) {
            return $this->adtsByFilePath[$filePath];
        }
        return null;
    }

    public function getAdtByFQN($fqn): ?Adt
    {
        if (array_key_exists($fqn, $this->adtsByFQN)) {
            return $this->adtsByFQN[$fqn];
        }
        return null;
    }

    public function getFilePaths()
    {
        return array_keys($this->adtsByFilePath);
    }

    public function hasFilePath($filePath)
    {
        return array_key_exists($filePath, $this->adtsByFilePath);
    }

    public function getDependenciesByFilePath($filePath)
    {
        $adt = $this->getAdtByFilePath($filePath);

        if (!$adt) {
            return [];
        }

        return array_filter(array_map(function($fqn) {
            /** @var FileMetadata $adt */
            $adt = $this->getAdtByFQN($fqn);
            if ($adt === null) {
                return null;
            }

            return $adt->getFilePath();
        }, $adt->getDependencies()), function($dep) {
            return $dep !== null;
        });
    }

    public function setDependencyFilePaths()
    {
        foreach ($this->adtsByFilePath as $adt) {
            /** @var FileMetadata $adt */

            foreach ($adt->getDependencies() as $fqn) {
                /** @var FileMetadata $dep */
                $dep = $this->getAdtByFQN($fqn);
                if ($dep) {
                    $adt->addDependencyFilePath($dep->getFilePath());
                }
            }
        }
    }

    public function __sleep()
    {
        return [
            'adtsByFilePath',
            'lastBuilt',
        ];
    }

    public function __wakeup()
    {
        foreach ($this->adtsByFile as $adt) {
            /** @var Adt $adt */
            $fqn = $adt->getFullyQualifiedName();
            if ($fqn !== null) {
                $this->adtsByFQN[$fqn] = $adt;
            }
        }
    }
}
