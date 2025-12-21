<?php

namespace Phpactor\Indexer\Model;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Phpactor\Indexer\Model\Record\FileRecord;

/**
 * @implements IteratorAggregate<RecordReference>
 */
class RecordReferences implements IteratorAggregate
{
    /**
     * @var array<RecordReference>
     */
    private array $references = [];

    /**
     * @param array<RecordReference> $references
     */
    public function __construct(
        private FileRecord $file,
        array $references
    ) {
        foreach ($references as $reference) {
            $this->add($reference);
        }
    }

    /**
     * @return RecordReferences<RecordReference>
     */
    public function to(Record $record): RecordReferences
    {
        return new self($this->file, array_filter($this->references, function (RecordReference $reference) use ($record) {
            return $reference->type() === $record->recordType() && $reference->identifier() === $record->identifier();
        }));
    }

    /**
     * @return Iterator<int, RecordReference>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->references);
    }

    /**
     * @return array<RecordReference>
     */
    public function toArray(): array
    {
        return $this->references;
    }

    public function forContainerType(string $fullyQualifiedName): self
    {
        return new self($this->file, array_filter($this->references, function (RecordReference $reference) use ($fullyQualifiedName) {
            return $fullyQualifiedName === $reference->contaninerType();
        }));
    }

    public function file(): FileRecord
    {
        return $this->file;
    }

    private function add(RecordReference $reference): void
    {
        $this->references[] = $reference;
    }
}
