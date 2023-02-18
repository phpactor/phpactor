<?php

namespace Phpactor\ClassMover\Domain\Reference;

class Position
{
    private function __construct(private int $start, private int $end)
    {
    }

    public static function fromStartAndEnd(int $start, int $end): self
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

    public function length(): int
    {
        return $this->end - $this->start;
    }
}
