<?php

namespace Phpactor\Extension\LanguageServer\Response;

class Range
{
    /**
     * @var Position
     */
    public $start;

    /**
     * @var Position
     */
    public $end;

    public function __construct(Position $start, Position $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}
