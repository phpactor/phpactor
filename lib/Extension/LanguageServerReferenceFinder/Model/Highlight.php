<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

class Highlight {
    public int $start;
    public int $end;

    public string $kind;

    /**
     * @param DocumentHighlightKind::* $kind
     */
    public function __construct(int $start, int $end, string $kind)
    {
        $this->start = $start;
        $this->end = $end;
        $this->kind = $kind;
    }
}
