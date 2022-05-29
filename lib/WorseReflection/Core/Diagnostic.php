<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\TextDocument\ByteOffsetRange;

class Diagnostic
{
    public const ERROR = 1;
    public const WARNING = 2;
    public const INFORMATION = 3;
    public const HINT = 4;

    private string $message;

    private int $severity;

    private ByteOffsetRange $range;

    public function __construct(
        ByteOffsetRange $range,
        string $message,
        int $severity
    ) {
        $this->message = $message;
        $this->severity = $severity;
        $this->range = $range;
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

    public static function severityAsString(int $severity): string
    {
        switch ($severity) {
            case self::HINT:
                return 'HINT';
            case self::ERROR:
                return 'ERROR';
            case self::WARNING:
                return 'WARN';
            case self::INFORMATION:
                return 'INFO';
        }
        return 'unknown';
    }
}
