<?php

namespace Phpactor\Search\Model;

interface MatchRenderer
{
    public function render(DocumentMatches $matches): void;
}
