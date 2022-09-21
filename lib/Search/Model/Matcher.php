<?php

namespace Phpactor\Search\Model;

interface Matcher
{
    const LABEL_PATTERN = '[a-zA-Z0-9_\x80-\xff][a-zA-Z_\x80-\xff]*';
    public function matches(MatchToken $token1, MatchToken $token2): MatchResult;
}
