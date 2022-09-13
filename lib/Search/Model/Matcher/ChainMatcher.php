<?php

namespace Phpactor\Search\Model\Matcher;

use Phpactor\Search\Model\MatchResult;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\Matcher;

class ChainMatcher implements Matcher
{
    /**
     * @var Matcher[]
     */
    private array $matchers;

    public function __construct(Matcher ...$matchers)
    {
        $this->matchers = $matchers;
    }

    public function matches(MatchToken $token1, MatchToken $token2): MatchResult
    {
        foreach ($this->matchers as $matcher) {
            $result = $matcher->matches($token1, $token2);
            if ($result->isMatch()) {
                return MatchResult::yes();
            }
            if ($result->isNotMatch()) {
                return MatchResult::no();
            }
        }

        return MatchResult::maybe();
    }
}
