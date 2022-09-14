<?php

namespace Phpactor\Search\Model;

use Phpactor\TextDocument\ByteOffsetRange;

class PatternMatch
{
    private ByteOffsetRange $range;

    private MatchTokens $matchTokens;

    public function __construct(ByteOffsetRange $range, MatchTokens $matchTokens)
    {
        $this->range = $range;
        $this->matchTokens = $matchTokens;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function matchTokens(): MatchTokens
    {
        return $this->matchTokens;
    }
}
