<?php

namespace Phpactor\Indexer\Model\Index;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\SearchIndex;
use SplFileInfo;

class SearchAwareIndex implements Index
{
    public function __construct(
        private readonly Index $innerIndex,
        private readonly SearchIndex $search
    ) {
    }

    public function lastUpdate(): int
    {
        return $this->innerIndex->lastUpdate();
    }

    public function write(Record $record): void
    {
        $this->innerIndex->write($record);
        $this->search->write($record);
    }

    public function isFresh(SplFileInfo $fileInfo): bool
    {
        return $this->innerIndex->isFresh($fileInfo);
    }

    public function reset(): void
    {
        $this->innerIndex->reset();
    }

    public function exists(): bool
    {
        return $this->innerIndex->exists();
    }

    public function done(): void
    {
        $this->innerIndex->done();
        $this->search->flush();
    }


    public function get(Record $record): Record
    {
        return $this->innerIndex->get($record);
    }

    public function has(Record $record): bool
    {
        return $this->innerIndex->has($record);
    }
}
