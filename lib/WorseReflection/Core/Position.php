<?php

namespace Phpactor\WorseReflection\Core;

final class Position
{
    private int $fullStart;
    
    private int $start;
    
    private int $end;

    private function __construct(
        int $fullStart,
        int $start,
        int $end
    ) {
        $this->fullStart = $fullStart;
        $this->start = $start;
        $this->end = $end;
    }

    public static function fromFullStartStartAndEnd(int $fullStart, int $start, int $end)
    {
        return new self($fullStart, $start, $end);
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
