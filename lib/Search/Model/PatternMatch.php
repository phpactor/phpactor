<?php

namespace Phpactor\Search\Model;

use Phpactor\TextDocument\ByteOffsetRange;

class PatternMatch
{
    private ByteOffsetRange $range;

    public function __construct(ByteOffsetRange $range)
    {
        $this->range = $range;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }
}
