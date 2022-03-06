<?php

namespace Phpactor\Indexer\Model;

class RecordReference
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var int
     */
    private $offset;

    /**
     * @var string|null
     */
    private $contaninerType;

    public function __construct(string $type, string $identifier, int $offset, ?string $contaninerType = null)
    {
        $this->type = $type;
        $this->identifier = $identifier;
        $this->offset = $offset;
        $this->contaninerType = $contaninerType;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function type(): string
    {
        return $this->type;
    }

    public static function fromRecordAndOffset(Record $record, int $offset): self
    {
        return new self($record->recordType(), $record->identifier(), $offset);
    }

    public static function fromRecordAndOffsetAndContainerType(Record $record, int $offset, ?string $containerType): self
    {
        return new self($record->recordType(), $record->identifier(), $offset, $containerType);
    }

    public function withContainerType(string $type): self
    {
        return new self($this->type, $this->identifier, $this->offset, $type);
    }

    public function contaninerType(): ?string
    {
        return $this->contaninerType;
    }
}
