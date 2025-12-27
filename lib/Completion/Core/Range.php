<?php

namespace Phpactor\Completion\Core;

use Phpactor\TextDocument\ByteOffset;

class Range
{
    public function __construct(
        private readonly ByteOffset $byteStart,
        private readonly ByteOffset $byteEnd
    ) {
    }

    public static function fromStartAndEnd(int $byteStart, int $byteEnd): self
    {
        return new self(
            ByteOffset::fromInt($byteStart),
            ByteOffset::fromInt($byteEnd)
        );
    }

    public function start(): ByteOffset
    {
        return $this->byteStart;
    }

    public function end(): ByteOffset
    {
        return $this->byteEnd;
    }

    public function toArray(): array
    {
        return [ $this->byteStart->toInt(), $this->byteEnd->toInt() ];
    }
}
