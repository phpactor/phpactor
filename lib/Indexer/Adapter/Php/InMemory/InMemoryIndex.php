<?php

namespace Phpactor\Indexer\Adapter\Php\InMemory;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Record;
use SplFileInfo;

class InMemoryIndex implements Index
{
    /**
     * @var int|null
     */
    private $lastUpdate;

    /**
     * @var InMemorySearchIndex
     */
    private $searchIndex;

    /**
     * @var array<Record>
     */
    private $index;

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

        if (isset($this->index[$key])) {
            /** @phpstan-ignore-next-line */
            return $this->index[$key];
        }

        return $record;
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
        return $record->recordType().$record->identifier();
    }
}
