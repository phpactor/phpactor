<?php

namespace Phpactor\TextDocument;

use OutOfBoundsException;
use RuntimeException;

/**
 * Value object for line / column position.
 *
 * Lines and columns start with 1.
 *
 * The "a" in "abcd" would have line "1" and column "1".
 */
final class LineCol
{
    public const NEWLINE_PATTERN = '\\r\\n|\\n|\\r';

    private int $line;

    private int $col;

    public function __construct(int $line, int $col)
    {
        if ($line < 1) {
            throw new RuntimeException(sprintf(
                'Line number cannot be less than 1 (got "%s")',
                $line
            ));
        }
        if ($col < 1) {
            throw new RuntimeException(sprintf(
                'Col number cannot be less than 1 (got "%s")',
                $col
            ));
        }
        $this->line = $line;
        $this->col = $col;
    }

    public function toByteOffset(string $text): ByteOffset
    {
        $linesAndDelims = (array)preg_split('{(' . self::NEWLINE_PATTERN . ')}', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($linesAndDelims) === 0) {
            return ByteOffset::fromInt(
                strlen((string)reset($linesAndDelims))
            );
        }

        $lineNb = 1;
        $offset = 0;
        foreach ($linesAndDelims as $lineOrDelim) {
            $lineOrDelim = (string)$lineOrDelim;

            if ((bool)preg_match('{(' . self::NEWLINE_PATTERN . ')}', (string)$lineOrDelim)) {
                $lineNb++;
                $offset += strlen($lineOrDelim);
                continue;
            }

            if ($lineNb === $this->line()) {
                $lineSection = mb_substr(
                    $lineOrDelim,
                    0,
                    $this->col() - 1
                );
                return ByteOffset::fromInt(
                    $offset + (int)strlen($lineSection)
                );
            }

            $offset += strlen((string)$lineOrDelim);
        }

        return ByteOffset::fromInt(strlen($text));
    }

    public static function fromByteOffset(string $text, ByteOffset $byteOffset, bool $lspPosition = false): self
    {
        if ($byteOffset->toInt() > strlen($text)) {
            $byteOffset = ByteOffset::fromInt(strlen($text));
        }

        $lines = preg_split('{(' . self::NEWLINE_PATTERN . ')}', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (false === $lines) {
            throw new RuntimeException(
                'Failed to preg-split text into lines'
            );
        }

        $offset = 0;
        $lineNb = 0;
        foreach ($lines as $lineOrDelim) {
            $lineOrDelim = (string)$lineOrDelim;

            if ((bool)preg_match('{(' . self::NEWLINE_PATTERN . ')}', (string)$lineOrDelim)) {
                $offset += strlen($lineOrDelim);
                continue;
            }
            $lineNb++;

            $start = $offset;
            $end = $offset + strlen($lineOrDelim);

            // if the offset is in line...
            if ($byteOffset->toInt() >= $start && $byteOffset->toInt() <= $end) {
                $section = substr(
                    $lineOrDelim,
                    0,
                    $byteOffset->toInt() - $start
                );
                if ($lspPosition) {
                    $utf16 = \mb_convert_encoding($section, 'UTF-16', 'UTF-8');
                    return new self(
                        $lineNb,
                        strlen($utf16) / 2 + 1,
                    );
                }

                return new self($lineNb, mb_strlen($section) + 1);
            }

            $offset = $end;
        }

        throw new OutOfBoundsException(sprintf(
            'Byte offset %s is larger than text length %s',
            $byteOffset->toInt(),
            strlen($text)
        ));
    }

    public function col(): int
    {
        return $this->col;
    }

    public function line(): int
    {
        return $this->line;
    }
}
