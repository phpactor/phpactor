<?php

namespace Phpactor\ClassMover\Domain\Reference;

class Position
{
    private $start;

    private $end;

    private function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public static function fromStartAndEnd(int $start, int $end)
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
