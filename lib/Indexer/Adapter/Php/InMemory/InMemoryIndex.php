<?php

namespace Phpactor\Indexer\Adapter\Php\InMemory;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Record;
use SplFileInfo;

class InMemoryIndex implements Index
{
    private ?int $lastUpdate;

    private InMemorySearchIndex $searchIndex;

    /**
     * @var array<Record>
     */
    private array $index;

    /**
     * @param array<Record> $index
     */
    public function __construct(array $index = [])
    {
        $this->searchIndex = new InMemorySearchIndex();
        $this->lastUpdate = 0;
        foreach ($index as $record) {
            $this->write($record);
        }
    }

    public function lastUpdate(): int
    {
        return $this->lastUpdate;
    }

    public function write(Record $record): void
    {
        $this->index[$this->recordKey($record)] = $record;
        $this->searchIndex->write($record);
    }

    public function get(Record $record): Record
    {
        $key = $this->recordKey($record);

        return $this->index[$key] ?? $record;
    }

    public function isFresh(SplFileInfo $fileInfo): bool
    {
        return false;
    }

    public function reset(): void
    {
        $this->index = [];
    }

    public function exists(): bool
    {
        return $this->lastUpdate !== 0;
    }

    public function done(): void
    {
        $this->lastUpdate = time();
    }

    public function has(Record $record): bool
    {
        return isset($this->index[$this->recordKey($record)]);
    }

    private function recordKey(Record $record): string
    {
        return $record->recordType()->value.$record->identifier();
    }
}
