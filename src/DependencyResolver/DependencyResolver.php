<?php

namespace DKoehn\DSpec\DependencyResolver;

use DKoehn\DSpec\Cache\DependencyCache;

class DependencyResolver
{
    /** @var DependencyCache */
    private $cache;

    public function __construct(DependencyCache $cache)
    {
        $this->cache = $cache;
    }

    public function resolveInverse(array $filePaths, callable $filter = null)
    {
        if (count($filePaths) === 0) {
            return [];
        }

        if ($filter === null) {
            $filter = function() {
                return true;
            };
        }

        $collectDependencies = function(array $relatedFilePaths, array $allFilePaths, array $changed) use ($filter) {
            $visitedFiles = [];
            $result = [];

            while (count($changed) > 0) {
                $changed = array_reduce($allFilePaths, function($acc, $filePath) use ($changed, &$visitedFiles, &$result, $filter, &$relatedFilePaths) {
                    $adt = $this->cache->getAdtByFilePath($filePath);

                    $deps = array_filter($adt->getDependencyFilePaths(), function($depFilePath) use ($changed) {
                        return isset($changed[$depFilePath]);
                    });

                    if (isset($visitedFiles[$filePath]) || count($deps) === 0) {
                        return $acc;
                    }

                    if ($filter($filePath)) {
                        $result[] = $filePath;
                        unset($relatedFilePaths[$filePath]);
                    }
                    $visitedFiles[$filePath] = true;
                    $acc[$filePath] = true;
                    return $acc;
                }, []);
            }

            return $result;
        };

        $changed = [];
        $relatedPaths = [];
        foreach ($filePaths as $filePath) {
            if ($this->cache->hasFilePath($filePath)) {
                $changed[$filePath] = true;
                if ($filter($filePath)) {
                    $relatedPaths[$filePath] = true;
                }
            }
        }

        return $collectDependencies($relatedPaths, $this->cache->getFilePaths(), $changed);
    }

    public function resolve($filePath): array
    {
        $dependencies = $this->cache->getDependenciesByFilePath($filePath);

        if (!$dependencies) {
            return [];
        }

        return array_reduce($dependencies, function($acc, $dependency) {
            return array_merge(
                $acc,
                $this->cache->getDependenciesByFilePath($dependency)
            );
        }, []);
    }
}
