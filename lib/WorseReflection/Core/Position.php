<?php

namespace Phpactor\WorseReflection\Core;

final class Position
{
    private function __construct(private int $start, private int $end)
    {
    }

    public static function fromInts(int $start, int $end): self
    {
        return new self($start, $end);
    }

    public function start(): int
    {
        return $this->start;
    }

    public function end(): int
    {
        return $this->end;
    }

    public function width(): int
    {
        return $this->end - $this->start;
    }
}
