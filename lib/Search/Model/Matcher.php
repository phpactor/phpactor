<?php

namespace Phpactor\Search\Model;

use Phpactor\TextDocument\TextDocument;

interface Matcher
{
    /**
     * Find all nodes matching first node of pattern
     * Within those nodes find immediate children matching secod node of pattern
     */
    public function match(TextDocument $document, string $pattern): Matches;
}
