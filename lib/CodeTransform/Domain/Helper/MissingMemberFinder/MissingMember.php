<?php

namespace Phpactor\CodeTransform\Domain\Helper\MissingMemberFinder;

use Phpactor\TextDocument\ByteOffsetRange;

class MissingMember
{
    public function __construct(
        private readonly string $name,
        public ByteOffsetRange $range,
        private readonly string $memberType
    ) {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function memberType(): string
    {
        return $this->memberType;
    }
}
