<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

use Phpactor\LanguageServerProtocol\DocumentHighlightKind;

class Highlight
{
    /**
     * @param DocumentHighlightKind::* $kind
     */
    public function __construct(
        public int $start,
        public int $end,
        public int $kind
    ) {
    }
}
