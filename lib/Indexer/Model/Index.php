<?php

namespace Phpactor\Indexer\Model;

use SplFileInfo;

interface Index extends IndexAccess
{
    public function lastUpdate(): int;

    public function write(Record $record): void;

    public function isFresh(SplFileInfo $fileInfo): bool;

    public function reset(): void;

    public function exists(): bool;

    public function done(): void;

    /**
     * Remove records referencing non-existing files etc.
     *
     * Returns NULL as a "tick" event to avoid blocking or an _informational_
     * string describing an optimization operation.
     *
     * @return iterable<string|null>
     */
    public function optimise(bool $dryRun): iterable;

}
