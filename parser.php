<?php

require_once 'vendor/autoload.php';

$start = microtime(true);

use DKoehn\DSpec\Cache\DependencyCache;

use Kwf\FileWatcher\Watcher;
use Kwf\FileWatcher\Event\Modify;
use Kwf\FileWatcher\Event\Create;
use Kwf\FileWatcher\Event\Delete;
use Kwf\FileWatcher\Event\Move;
use Kwf\FileWatcher\Event\QueueFull;
use DKoehn\DSpec\Parser\CachedParser;
use Symfony\Component\Finder\Finder;
use DKoehn\DSpec\Watcher\FileWatcher;

// Cache directory
// If not exists then build cache from scratch
// If exists then load cache, get last mtime, find all files / dirs in $paths that have mtime after the last mtime from cache
// Check for git modified files: `git ls-files --other --modified --exclude-standard` (in the $paths)
// If modified files, then get a list of tests to run
// If no modified files, then begin watching

$paths = ['src-examples', 'spec'];
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
foreach ($finder->files()->in(__DIR__ . '/src-examples') as $file) {
    /** @var \SplFileInfo $file */
    $filePaths[] = $file->getPath() . '/' . $file->getFilename();
}

$cachedParser = new CachedParser;
if (count($filePaths)) {
    $cachedParser->parse($cache, $filePaths);
    file_put_contents($cacheFile, serialize($cache));
}

// print_r($cache);

$watcher = new FileWatcher($cache, $cachedParser, $cacheFile);
$watcher->watch(__DIR__ . '/src-examples');

// echo (microtime(true) - $start) . "\n";