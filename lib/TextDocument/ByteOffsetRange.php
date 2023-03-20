<?php

namespace Phpactor\TextDocument;

class ByteOffsetRange
{
    public function __construct(private ByteOffset $start, private ByteOffset $end)
    {
    }

    public static function fromInts(int $start, int $end): self
    {
        return new self(
            ByteOffset::fromInt($start),
            ByteOffset::fromInt($end)
        );
    }

    public static function fromByteOffsets(ByteOffset $start, ByteOffset $end): self
    {
        return new self($start, $end);
    }

    public function start(): ByteOffset
    {
        return $this->start;
    }

    public function end(): ByteOffset
    {
        return $this->end;
    }

    public function startAsInt(): int
    {
        return $this->start->toInt();
    }

    public function endAsInt(): int
    {
        return $this->end->toInt();
    }
}
