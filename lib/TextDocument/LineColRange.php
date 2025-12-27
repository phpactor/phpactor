<?php

namespace Phpactor\TextDocument;

final class LineColRange
{
    public function __construct(
        private readonly LineCol $start,
        private readonly LineCol $end
    ) {
    }

    public function start(): LineCol
    {
        return $this->start;
    }

    public function end(): LineCol
    {
        return $this->end;
    }
}
