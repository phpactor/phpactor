<?php

namespace Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;

use Phpactor\TextDocument\ByteOffsetRange;

class MissingMethod
{
    public ByteOffsetRange $range;

    private string $name;

    public function __construct(string $name, ByteOffsetRange $range)
    {
        $this->name = $name;
        $this->range = $range;
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
