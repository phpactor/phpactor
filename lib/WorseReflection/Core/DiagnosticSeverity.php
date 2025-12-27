<?php

namespace Phpactor\WorseReflection\Core;

use RuntimeException;

final class DiagnosticSeverity
{
    public const ERROR = 1;
    public const WARNING = 2;
    public const INFORMATION = 3;
    public const HINT = 4;

    /**
     * @param self::* $level
     */
    private function __construct(private readonly int $level)
    {
        $validLevels = [
            self::INFORMATION,
            self::WARNING,
            self::ERROR,
            self::HINT
        ];
        if (!in_array($level, $validLevels)) {
            throw new RuntimeException(sprintf(
                'Severity must be one of DiagnosticSeverity::*, got: %d (valid values: %s)',
                $level,
                implode(', ', $validLevels)
            ));
        }
    }

    public static function ERROR(): self
    {
        return new self(self::ERROR);
    }

    public static function INFORMATION(): self
    {
        return new self(self::INFORMATION);
    }

    public static function WARNING(): self
    {
        return new self(self::WARNING);
    }

    public static function HINT(): self
    {
        return new self(self::HINT);
    }

    public function toString(): string
    {
        switch ($this->level) {
            case self::HINT:
                return 'HINT';
            case self::ERROR:
                return 'ERROR';
            case self::WARNING:
                return 'WARN';
            case self::INFORMATION:
                return 'INFO';
        }
    }

    public function isError(): bool
    {
        return $this->level === self::ERROR;
    }

    public function isWarning(): bool
    {
        return $this->level === self::WARNING;
    }

    public function isHint(): bool
    {
        return $this->level === self::HINT;
    }
}
