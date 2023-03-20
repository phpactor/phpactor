<?php

namespace Phpactor\TextDocument;

final class ByteOffsetRange
{
    private function __construct(private int $start, private int $end)
    {
    }

    public static function fromInts(int $start, int $end): self
    {
        return new self($start, $end);
    }

    public function startAsInt(): int
    {
        return $this->start;
    }

    public function endAsInt(): int
    {
        return $this->end;
    }
}
