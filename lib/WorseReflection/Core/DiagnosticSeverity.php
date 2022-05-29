<?php

namespace Phpactor\WorseReflection\Core;

final class DiagnosticSeverity
{
    public const ERROR = 1;
    public const WARNING = 2;
    public const INFORMATION = 3;
    public const HINT = 4;

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
