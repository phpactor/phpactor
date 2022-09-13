<?php

namespace Phpactor\Search\Adapter\TolerantParser\Matcher;

use Phpactor\Search\Adapter\TolerantParser\Matcher;
use Phpactor\Search\Model\MatchToken;

class TokenEqualityMatcher implements Matcher
{
    public function matches(MatchToken $token1, MatchToken $token2): bool
    {
        return $token1->kind === $token2->kind && $token1->text === $token2->text;
    }
}
