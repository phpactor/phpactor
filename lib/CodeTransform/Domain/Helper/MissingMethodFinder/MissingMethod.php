<?php

namespace Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;

use Phpactor\TextDocument\ByteOffsetRange;

class MissingMethod
{
    public ByteOffsetRange $range;

    private string $name;

    private string $classFqn;

    public function __construct(string $name, ByteOffsetRange $range, string $classFqn)
    {
        $this->name = $name;
        $this->range = $range;
        $this->classFqn = $classFqn;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function classFqn(): string
    {
        return $this->classFqn;
    }
}
