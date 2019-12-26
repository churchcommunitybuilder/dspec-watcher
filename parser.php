<?php

require_once 'vendor/autoload.php';

$start = microtime(true);

use CCB\DSpec\Cache\DependencyCache;

use CCB\DSpec\Parser\CachedParser;
use Symfony\Component\Finder\Finder;
use CCB\DSpec\Watcher\FileWatcher;

// Cache directory
// If not exists then build cache from scratch
// If exists then load cache, get last mtime, find all files / dirs in $paths that have mtime after the last mtime from cache
// Check for git modified files: `git ls-files --other --modified --exclude-standard` (in the $paths)
// If modified files, then get a list of tests to run
// If no modified files, then begin watching

$paths = ['../churchcommunitybuilder/app/src', '../churchcommunitybuilder/app/spec'];
// $paths = ['src-examples', 'spec'];
$cacheFile = './.dspec_cache';

if (file_exists($cacheFile)) {
    echo "Loading from cache file...\n";
    /** @var DependencyCache $cache */
    $cache = @unserialize(file_get_contents($cacheFile));
}
if (!isset($cache)) {
    echo "Initializing cache...\n";
    $cache = new DependencyCache;
    $lastModifiedTime = null;
} else {
    $lastModifiedTime = new DateTime();
    $lastModifiedTime->setTimestamp($cache->lastBuilt);
}

$finder = new Finder();
$finder->name('*.php');
if ($lastModifiedTime !== null) {
    $finder->date('since ' . $lastModifiedTime->format('Y-m-d H:i:s'));
}

$filePaths = [];
foreach ($finder->files()->in($paths) as $file) {
    /** @var \SplFileInfo $file */
    $filePaths[] = $file->getPath() . '/' . $file->getFilename();
}

$cachedParser = new CachedParser;
if (count($filePaths)) {
    $cachedParser->parse($cache, $filePaths);
    file_put_contents($cacheFile, serialize($cache));
}

echo "Cache update took: " . (microtime(true) - $start) . "\n";

// print_r($cache);

$watcher = new FileWatcher($cache, $cachedParser, $cacheFile);
$watcher->watch($paths);

// echo (microtime(true) - $start) . "\n";