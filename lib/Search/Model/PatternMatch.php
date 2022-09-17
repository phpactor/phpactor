<?php

namespace Phpactor\Search\Model;

use Phpactor\TextDocument\ByteOffsetRange;

final class PatternMatch
{
    private ByteOffsetRange $range;

    private MatchTokens $tokens;

    public function __construct(ByteOffsetRange $range, MatchTokens $tokens)
    {
        $this->range = $range;
        $this->tokens = $tokens;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function tokens(): MatchTokens
    {
        return $this->tokens;
    }

    public function withTokens(MatchTokens $tokens): self
    {
        return new self($this->range, $tokens);
    }

    public function hasDepletedPlaceholders(): bool
    {
        return $this->tokens->hasDepletedPlaceholders();
    }
}
