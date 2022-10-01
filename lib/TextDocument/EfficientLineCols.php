<?php

namespace Phpactor\TextDocument;

use OutOfBoundsException;
use RuntimeException;

class LineCols
{
    /**
     * @var array<int,LineCol>
     */
    private array $positions = [];

    /**
     * @param array<int,LineCol> $positions
     */
    public function __construct(array $positions)
    {
        $this->positions = $positions;
    }

    public static function fromByteOffsetInts(string $text, array $ints): self
    {
        sort($ints);

        $lines = preg_split('{(' . LineCol::NEWLINE_PATTERN . ')}', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (false === $lines) {
            throw new RuntimeException(
                'Failed to preg-split text into lines'
            );
        }

        $offset = 0;
        $lineNb = 0;
        $byteOffset = array_shift($ints);
        if (null === $byteOffset) {
            return new LineCols([]);
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

                $positions[$byteOffset] = new LineCol($lineNb, mb_strlen($section) + 1);
                $byteOffset = array_shift($ints);
                if (null === $byteOffset) {
                    break;
                }
            }

            $offset = $end;
        }

        return new LineCols($positions);
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
