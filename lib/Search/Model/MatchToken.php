<?php

namespace Phpactor\Search\Model;

use Phpactor\TextDocument\ByteOffsetRange;

class MatchToken
{
    public ByteOffsetRange $range;
    public string $text;
    public int $kind;

    public function __construct(ByteOffsetRange $range, string $text, int $kind)
    {
        $this->range = $range;
        $this->text = $text;
        $this->kind = $kind;
    }
}
