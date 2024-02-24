<?php

namespace Phpactor\TextDocument;

use OutOfBoundsException;
use RuntimeException;

/**
 * Efficiently compute line/col positions for batches of byte offsets.
 *
 * This class accepts a list of byte offsets and a text document. It will
 * iterate over the text document _once_ indexing the line/col position of the
 * byte offset.
 */
final class EfficientLineCols
{
    /**
     * @param array<int,LineCol> $positions
     */
    private function __construct(private array $positions)
    {
    }

    /**
     * Initialize the converter with the list of byte offsets to be converted.
     *
     * For converting to LSP positions there is a flag to return the character
     * offset. Note that, unlike the line/col number, this is 1 based and is based
     * on UTF-16 code units.
     *
     * @param list<int> $byteOffsetInts
     */
    public static function fromByteOffsetInts(
        string $text,
        array $byteOffsetInts,
        bool $lspPosition = false
    ): self {
        sort($byteOffsetInts);

        $lines = preg_split('{(' . LineCol::NEWLINE_PATTERN . ')}', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (false === $lines) {
            throw new RuntimeException(
                'Failed to preg-split text into lines'
            );
        }

        $offset = 0;
        $lineNb = 0;
        $byteOffset = array_shift($byteOffsetInts);
        if (null === $byteOffset) {
            return new EfficientLineCols([]);
        }
        $positions = [];

        foreach ($lines as $lineOrDelim) {
            $lineOrDelim = (string)$lineOrDelim;

            if ((bool)preg_match('{(' . LineCol::NEWLINE_PATTERN . ')}', (string)$lineOrDelim)) {
                $offset += strlen($lineOrDelim);
                continue;
            }
            $lineNb++;

            $start = $offset;
            $end = $offset + strlen($lineOrDelim);

            while ($byteOffset >= $start && $byteOffset <= $end) {
                $section = substr(
                    $lineOrDelim,
                    0,
                    $byteOffset - $start
                );

                if ($lspPosition) {
                    $utf16 = \mb_convert_encoding($section, 'UTF-16', 'UTF-8');
                    $positions[$byteOffset] = new LineCol(
                        $lineNb,
                        strlen($utf16) / 2 + 1,
                    );
                } else {
                    $positions[$byteOffset] = new LineCol(
                        $lineNb,
                        strlen($section) + 1,
                    );
                }
                $byteOffset = array_shift($byteOffsetInts);
                if (null === $byteOffset) {
                    break;
                }
            }

            $offset = $end;
        }

        /** @var array<int,LineCol> $positions */
        return new EfficientLineCols($positions);
    }

    public function get(int $offset): LineCol
    {
        if (!isset($this->positions[$offset])) {
            throw new OutOfBoundsException(sprintf(
                'Pre-computed position not known for offset: %s',
                $offset
            ));
        }

        return $this->positions[$offset];
    }
}
