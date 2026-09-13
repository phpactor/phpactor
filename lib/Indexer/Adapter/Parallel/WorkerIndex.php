<?php

namespace Phpactor\Indexer\Adapter\Parallel;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Record;
use SplFileInfo;

/**
 * The index a parallel worker builds its subset of the project into.
 */
final class WorkerIndex implements Index
{
    /**
     * @var array<string,Record>
     */
    private array $records = [];

    public function lastUpdate(): int
    {
        return 0;
    }

    public function write(Record $record): void
    {
        $this->records[$this->recordKey($record)] = $record;
    }

    public function get(Record $record): Record
    {
        /** @phpstan-ignore-next-line */
        return $this->records[$this->recordKey($record)] ?? $record;
    }

    public function has(Record $record): bool
    {
        return isset($this->records[$this->recordKey($record)]);
    }

    public function isFresh(SplFileInfo $fileInfo): bool
    {
        return false;
    }

    public function reset(): void
    {
        $this->records = [];
    }

    public function exists(): bool
    {
        return false;
    }

    public function done(): void
    {
    }

    public function optimise(bool $dryRun): iterable
    {
        return [];
    }

    /**
     * @return list<Record>
     */
    public function records(): array
    {
        return array_values($this->records);
    }

    private function recordKey(Record $record): string
    {
        return $record->recordType() . $record->identifier();
    }
}
