<?php

namespace Phpactor\Search\Model;

use Phpactor\TextDocument\TextDocument;

interface MatchRenderer
{
    public function render(TextDocument $document, PatternMatch $match): string;
}
