<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Phpactor\Search\Model\MatchToken;

interface Matcher
{
    public function matches(MatchToken $token1, MatchToken $token2): bool;
}
