<?php

namespace Phpactor\Search\Model;

interface MatchFilter
{
    public function filter(DocumentMatches $matches, TokenConstraints $filter): DocumentMatches;
}
