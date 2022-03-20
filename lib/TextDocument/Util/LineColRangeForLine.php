<?php

namespace Phpactor\TextDocument\Util;

use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\LineColRange;
use RuntimeException;

final class LineColRangeForLine
{
    public function rangeFromLine(string $text, int $lineNo): LineColRange
    {
        $lines = preg_split("{(\r\n|\n|\r)}", $text, -1);

        if (false === $lines) {
            throw new RuntimeException(
                'Failed to preg-split text into lines'
            );
        }

        // if out of range, let the caller deal with it
        if (!isset($lines[$lineNo - 1])) {
            return new LineColRange(
                new LineCol($lineNo, 1),
                new LineCol($lineNo, 1)
            );
        }

        $line = $lines[$lineNo - 1];

        // if an empty line, return the first char
        if (!$line) {
            return new LineColRange(
                new LineCol($lineNo, 1),
                new LineCol($lineNo, 1)
            );
        }

        $start = mb_strpos($line, trim($line));
        $end = $start + mb_strlen(trim($line));

        return new LineColRange(
            new LineCol($lineNo, $start + 1),
            new LineCol($lineNo, $end)
        );
    }
}
