<?php

namespace Phpactor\WorseReflection\Core;

final class Position
{
    private function __construct(private int $fullStart, private int $start, private int $end)
    {
    }

    public static function fromStartAndEnd(int $start, int $end)
    {
        return new self($start, $start, $end);
    }

    public function fullStart(): int
    {
        return $this->fullStart;
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

    public function fullWidth(): int
    {
        return $this->end - $this->fullStart;
    }
}
