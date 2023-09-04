<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\HasFlags;
use Phpactor\Indexer\Model\Record\HasFlagsTrait;

class RecordReference implements HasFlags
{
    use HasFlagsTrait;
    const FLAG_NEW_OBJECT = 1;

    public function __construct(
        private string $type,
        private string $identifier,
        private int $start,
        private int $end,
        private ?string $contaninerType = null,
        int $flags = 0
    ) {
        $this->flags = $flags;
    }

    public function start(): int
    {
        return $this->start;
    }

    public function end(): int
    {
        return $this->end;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function type(): string
    {
        return $this->type;
    }

    public static function fromRecordAndOffsetAndContainerType(
        Record $record,
        int $start,
        int $end,
        ?string $containerType
    ): self {
        return new self($record->recordType(), $record->identifier(), $start, $end, $containerType);
    }

    public function withContainerType(string $type): self
    {
        return new self($this->type, $this->identifier, $this->start, $this->end, $type);
    }

    public function contaninerType(): ?string
    {
        return $this->contaninerType;
    }
}
