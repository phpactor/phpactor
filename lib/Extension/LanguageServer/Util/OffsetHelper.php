<?php

namespace Phpactor\Extension\LanguageServer\Util;

use OutOfBoundsException;

class OffsetHelper
{
    public static function lineAndCharacterNumberToOffset(string $text, int $lineNb, int $col): int
    {
        if ($lineNb < 0) {
            throw new OutOfBoundsException(sprintf(
                'Line number cannot be negative, got "%s"',
                $lineNb
            ));
        }

        if ($col < 0) {
            throw new OutOfBoundsException(sprintf(
                'Col number cannot be negative, got "%s"',
                $lineNb
            ));
        }

        $lines = explode(PHP_EOL, $text);

        if ($lineNb > count($lines)) {
            throw new OutOfBoundsException(sprintf(
                'Invalid line number ("%s") for text with %s lines',
                $lineNb,
                count($lines)
            ));
        }

        $line = $lines[$lineNb];

        $line = mb_substr($line, 0, $col);

        if ($col > mb_strlen($line)) {
            throw new OutOfBoundsException(sprintf(
                'Invalid character offset "%s" for line of length "%s"',
                $col,
                mb_strlen($line)
            ));
        }

        $lines = array_slice($lines, 0, $lineNb);
        $text = implode(PHP_EOL, $lines);

        return mb_strlen($text) + mb_strlen($line) + 1;
    }
}
