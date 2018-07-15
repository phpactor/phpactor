<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

use Phpactor\Extension\LanguageServer\Protocol\Position;

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
