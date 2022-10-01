<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

use Phpactor\LanguageServerProtocol\DocumentHighlightKind;

class Highlight
{
    public int $start;

    public int $end;

    /**
     * @var DocumentHighlightKind::*
     */
    public int $kind;

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
