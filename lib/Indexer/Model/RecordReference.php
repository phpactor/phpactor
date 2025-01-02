<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\HasFlags;
use Phpactor\Indexer\Model\Record\HasFlagsTrait;
use Phpactor\Indexer\Model\Record\RecordType;

class RecordReference implements HasFlags
{
    use HasFlagsTrait;
    public const FLAG_NEW_OBJECT = 1;

    /**
     * Order matters here for B/C, new parameters must be added after old ones.
     */
    public function __construct(
        private RecordType $type,
        private string $identifier,
        private int $start,
        private ?string $contaninerType = null,
        int $flags = 0,
        private ?int $end = null,
    ) {
        $this->flags = $flags;
    }

    public function start(): int
    {
        return $this->start;
    }

    public function end(): int
    {
        return $this->end ?? $this->start;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function type(): RecordType
    {
        return $this->type;
    }

    public static function fromRecordAndOffsetAndContainerType(
        Record $record,
        int $start,
        int $end,
        ?string $containerType
    ): self {
        return new self(
            $record->recordType(),
            $record->identifier(),
            $start,
            $containerType,
            0,
            $end,
        );
    }

    public function withContainerType(string $type): self
    {
        return new self(
            $this->type,
            $this->identifier,
            $this->start,
            $type,
            $this->flags,
            $this->end,
        );
    }

    public function contaninerType(): ?string
    {
        return $this->contaninerType;
    }
}
