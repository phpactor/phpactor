<?php

namespace Phpactor\Search\Model\Filter;

use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;

class PassthroughMatchFilter implements MatchFilter
{
    public function filter(DocumentMatches $matches, string $filter): DocumentMatches
    {
        return $matches;
    }
}
