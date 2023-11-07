<?php

namespace Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;

use Phpactor\TextDocument\ByteOffsetRange;

class MissingMember
{
    public function __construct(private string $name, public ByteOffsetRange $range)
    {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function name(): string
    {
        return $this->name;
    }
}
