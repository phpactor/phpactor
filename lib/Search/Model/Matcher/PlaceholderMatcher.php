<?php

namespace Phpactor\Search\Model\Matcher;

use Phpactor\Search\Model\MatchResult;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\Matcher;

class PlaceholderMatcher implements Matcher
{
    private string $template;

    public function __construct(string $template = '^\$?__(?P<placeholder>[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)__')
    {
        $this->template = $template;
    }

    public function matches(MatchToken $token1, MatchToken $token2): MatchResult
    {
        if (preg_match('{' . $this->template . '}', $token2->text, $matches)) {
            return MatchResult::yes($token1, $matches['placeholder'] ?? null);
        }

        return MatchResult::maybe();
    }
}
