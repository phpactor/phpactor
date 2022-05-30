<?php

namespace Phpactor\WorseReflection\Core;

final class DiagnosticSeverity
{
    public const ERROR = 1;
    public const WARNING = 2;
    public const INFORMATION = 3;
    public const HINT = 4;

    /**
     * @var self::*
     */
    private $level;

    /**
     * @param self::* $level
     */
    private function __construct(int $level)
    {
        $this->level = $level;
    }

    public static function ERROR(): self
    {
        return new self(self::ERROR);
    }

    public static function WARNING(): self
    {
        return new self(self::WARNING);
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
        return 'unknown';
    }
}
