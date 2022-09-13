<?php

namespace Phpactor\Search\Adapter\TolerantParser\Matcher;

use Phpactor\Search\Model\MatchResult;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\Matcher;

class PlaceholderMatcher implements Matcher
{
    private string $pattern;

    public function __construct(string $pattern = '^\$?__(?P<label>[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)__')
    {
        $this->pattern = $pattern;
    }

    public function matches(MatchToken $token1, MatchToken $token2): MatchResult
    {
        if (preg_match('{' . $this->pattern . '}', $token2->text, $matches)) {
            return MatchResult::yes();
        }
        return MatchResult::maybe();
    }
}
