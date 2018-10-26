<?php

namespace Phpactor\Extension\LanguageServer\Helper;

use LanguageServerProtocol\Position;

class OffsetHelper
{
    public static function offsetToPosition(string $text, int $offset): Position
    {
        $text = mb_substr($text, 0, $offset);
        $line = mb_substr_count($text, PHP_EOL);

        if ($line === 0) {
            return new Position($line, mb_strlen($text));
        }

        $lastNewLinePos = strrpos($text, PHP_EOL);
        $remainingLine = mb_substr($text, $lastNewLinePos + mb_strlen(PHP_EOL));
        $char = mb_strlen($remainingLine);

        return new Position($line, $char);
    }
}
