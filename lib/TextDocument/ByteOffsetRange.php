<?php

namespace Phpactor\TextDocument;

use Stringable;

class ByteOffsetRange implements Stringable
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

    public function __toString(): string
    {
        return sprintf('%s-%s', $this->start->toInt(), $this->end->toInt());
    }
}
