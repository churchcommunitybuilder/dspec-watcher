<?php

namespace DKoehn\DSpec\Cache;

use DKoehn\DSpec\Parser\Adt;


class DependencyCache
{
    public $adtsByFile = [];
    public $lastBuilt = null;

    public $adtsByFQN = [];

    public function setLastBuilt()
    {
        $this->lastBuilt = time();
    }

    public function getReferencesForFile($file)
    {
        $adt = $this->getAdtByFile($file);

        if ($adt === null) {
            return [];
        }

        $references = [];
        foreach ($this->adtsByFQN as $ref) {
            /** @var Adt $ref */
            if ($ref->hasDependency($adt)) {
                $references[] = $ref;
            }
        }

        return $references;
    }

    public function getReferencesByFQN(string $dependency): array
    {
        $adt = $this->getAdtByFQN($dependency);

        if ($adt === null) {
            return [];
        }

        $references = [];
        foreach ($this->adtsByFQN as $ref) {
            /** @var Adt $ref */
            if ($ref->hasDependency($adt)) {
                $references[] = $ref;
            }
        }

        return $references;
    }

    public function getAdtByFile(string $file): ?Adt
    {
        if (!array_key_exists($file, $this->adtsByFile)) {
            return null;
        }

        return $this->adtsByFile[$file];
    }

    public function getAdtByFQN(string $dependency): ?Adt
    {
        if (!array_key_exists($dependency, $this->adtsByFQN)) {
            return null;
        }

        return $this->adtsByFQN[$dependency];
    }

    public function __sleep()
    {
        return [
            'adtsByFile',
            'lastBuilt',
        ];
    }

    public function __wakeup()
    {
        foreach ($this->adtsByFile as $adt) {
            /** @var Adt $adt */
            $this->adtsByFQN[$adt->getFullyQualifiedName()] = $adt;
        }
    }
}
