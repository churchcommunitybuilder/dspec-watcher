<?php

namespace DKoehn\DSpec\Watcher;

use Kwf\FileWatcher\Watcher;
use Kwf\FileWatcher\Event\Modify;
use Kwf\FileWatcher\Event\Create;
use Kwf\FileWatcher\Event\Delete;
use Kwf\FileWatcher\Event\Move;
use Kwf\FileWatcher\Event\QueueFull;

use DKoehn\DSpec\Cache\DependencyCache;
use DKoehn\DSpec\Parser\CachedParser;
use Kwf\FileWatcher\Event\AbstractEvent;
use DKoehn\DSpec\Parser\Adt;
use DKoehn\DSpec\TestRunner\TestRunner;
use Symfony\Component\Console\Output\OutputInterface;

class FileWatcher
{
    /** @var DependencyCache */
    protected $cache;

    /** @var CachedParser */
    protected $cachedParser;

    /** @var TestRunner */
    protected $testRunner;
    /** @var OutputInterface */
    protected $output;

    protected $cacheFile;

    public function __construct(
        DependencyCache $cache,
        CachedParser $cachedParser,
        TestRunner $testRunner,
        OutputInterface $output,
        string $cacheFile
    ) {
        $this->cache = $cache;
        $this->cachedParser = $cachedParser;
        $this->testRunner = $testRunner;
        $this->output = $output;
        $this->cacheFile = $cacheFile;
    }

    public function onModify(AbstractEvent $e)
    {
        $this->parseFile($e->filename);
    }

    public function onDelete(Delete $e)
    {
        $this->removeFile($e->filename);
        // TODO: Handle delete
    }

    public function onMove(Move $e)
    {
        $this->removeFile($e->filename);
        $this->parseFile($e->destFilename);
    }

    protected function removeFile($filePath)
    {
        unset($this->cache->adtsByFile[$filePath]);
    }

    protected function parseFile($filePath)
    {
        $this->cachedParser->parse($this->cache, [$filePath]);

        file_put_contents($this->cacheFile, serialize($this->cache));

        $refs = $this->cache->getReferencesForFile($filePath);
        $allRefs = $refs;
        foreach ($refs as $ref) {
            /** @var Adt $ref */
            $allRefs = array_merge(
                $allRefs,
                $this->cache->getReferencesByFQN($ref->getFullyQualifiedName())
            );
        }

        $reducer = function($refs, Adt $adt) use (&$reducer) {
            $newRefs = $this->cache->getReferencesByFQN($adt->getFullyQualifiedName());

            foreach ($newRefs as $ref) {
                if (!in_array($ref, $refs)) {
                    $refs[] = $ref;
                    $refs = array_reduce($refs, $reducer, $refs);
                }
            }

            return $refs;
        };

        $allRefs = array_reduce($refs, $reducer, $refs);

        $this->testRunner->runTestsForRefs($allRefs);
    }

    public function watch($paths)
    {
        $watcher = Watcher::create($paths);
        $watcher->addListener(Modify::NAME, [$this, 'onModify']);
        $watcher->addListener(Create::NAME, [$this, 'onModify']);
        $watcher->addListener(Delete::NAME, [$this, 'onDelete']);
        $watcher->addListener(Move::NAME, [$this, 'onMove']);
        // $watcher->addListener(QueueFull::NAME, function(QueueFull $e) use ($watcher) {
        //     // TODO: Too many changes, should stop and re-process?
        //     // $watcher->stop();
        // });

        $this->output->write("\033\143");
        $this->output->writeln('Watching files for changes...');

        $watcher->start();
    }
}
