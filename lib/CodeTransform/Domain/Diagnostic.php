<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\TextDocument\ByteOffsetRange;

class Diagnostic
{
    public const ERROR = 1;
    public const WARNING = 2;
    public const INFORMATION = 3;
    public const HINT = 4;

    public function __construct(
        private ByteOffsetRange $range,
        private string $message,
        private int $severity
    ) {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): int
    {
        return $this->severity;
    }

    public function message(): string
    {
        return $this->message;
    }
}
