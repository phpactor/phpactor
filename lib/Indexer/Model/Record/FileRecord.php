<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\Exception\CorruptedRecord;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\RecordReferences;
use SplFileInfo;

class FileRecord implements HasPath, Record
{
    use HasPathTrait;

    /**
     * @var array<array{RecordType,string,int, ?string,int, int}>
     */
    private array $references = [];

    private function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function __wakeup(): void
    {
        if (null === $this->filePath) {
            throw new CorruptedRecord(sprintf(
                'Record was corrupted'
            ));
        }
    }


    public function recordType(): RecordType
    {
        return RecordType::FILE;
    }

    public static function fromFileInfo(SplFileInfo $info): self
    {
        return new self($info->getPathname());
    }

    public static function fromPath(string $path): self
    {
        return new self($path);
    }

    public function identifier(): string
    {
        return $this->filePath();
    }

    public function addReference(RecordReference $reference): self
    {
        $this->references[] = [
            $reference->type(),
            $reference->identifier(),
            $reference->start(),
            $reference->contaninerType(),
            $reference->flags(),
            $reference->end(),
        ];

        return $this;
    }

    public function references(): RecordReferences
    {
        return new RecordReferences($this, array_map(function (array $reference) {
            return new RecordReference(...$reference);
        }, $this->references));
    }

    public function removeReferencesToRecordType(RecordType $type): self
    {
        $this->references = array_filter($this->references, function (array $reference) use ($type) {
            return $reference[0] !== $type;
        });
        return $this;
    }
}
