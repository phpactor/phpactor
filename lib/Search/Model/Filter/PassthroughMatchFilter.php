<?php

namespace Phpactor\Search\Model\Filter;

use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\Search\Model\TokenConstraints;

class PassthroughMatchFilter implements MatchFilter
{
    public function filter(DocumentMatches $matches, TokenConstraints $constraints): DocumentMatches
    {
        return $matches;
    }
}
