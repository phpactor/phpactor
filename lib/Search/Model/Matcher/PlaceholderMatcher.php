<?php

namespace Phpactor\Search\Model\Matcher;

use Phpactor\Search\Model\MatchResult;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\Matcher;

class PlaceholderMatcher implements Matcher
{
    private const PATTERN = '^\$?__(?P<placeholder>' . Matcher::LABEL_PREFIX . Matcher::LABEL_SUFFIX . '*)__';

    public function matches(MatchToken $token1, MatchToken $token2): MatchResult
    {
        if (preg_match('{' . self::PATTERN . '}', $token2->text, $matches)) {
            return MatchResult::yes($token1, $matches['placeholder'] ?? null);
        }

        return MatchResult::maybe();
    }
}
