<?php

namespace Phpactor\Search\Model;

interface Matcher
{
    public function matches(MatchToken $token1, MatchToken $token2): MatchResult;
}
