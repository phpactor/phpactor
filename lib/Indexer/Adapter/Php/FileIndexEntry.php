<?php

declare(strict_types=1);

namespace Phpactor\Indexer\Adapter\Php;

use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\HasFlags;
use Phpactor\Indexer\Model\Record\RecordType;

class FileIndexEntry
{
    public const DELIMITER = "\t";

    private ?string $classType = null;

    private ?int $flag = null;

    public function __construct(
        private RecordType $recordType,
        private string $identifier,
    ) {
    }

    public function __toString(): string
    {
        return implode(self::DELIMITER, [
            $this->recordType->value,
            $this->identifier,
            $this->classType,
            $this->flag,
        ]);
    }

    public static function fromRecord(Record $record): self
    {
        $result = new self($record->recordType(), $record->identifier());
        if ($record instanceof ClassRecord) {
            $result->classType = $record->type();
        }
        if ($record instanceof HasFlags) {
            $result->flag = $record->flags();
        }
        return $result;
    }

    public static function fromString(string $entry): self
    {
        $parts = explode(self::DELIMITER, $entry);


        $result = new self(RecordType::from($parts[0]), $parts[1]);
        $result->classType = $parts[2] ?? null;
        if (array_key_exists(3, $parts)) {
            $result->flag = (int) $parts[3];
        }

        return $result;
    }

    public function recordType(): RecordType
    {
        return $this->recordType;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function classType(): string
    {
        return $this->classType ?? '';
    }

    public function flags(): int
    {
        return $this->flag ?? 0;
    }
}
