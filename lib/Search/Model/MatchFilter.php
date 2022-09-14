<?php

namespace Phpactor\Search\Model;

interface MatchFilter
{
    public function filter(DocumentMatches $matches, string $filter): DocumentMatches;
}
