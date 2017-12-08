<?php

namespace Phpactor\Core;

class OffsetContext
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var int
     */
    private $col;

    /**
     * @var string
     */
    private $line;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var string
     */
    private $selected;

    private function __construct(int $offset, int $lineNumber, int $col, string $line, string $selected)
    {
        $this->lineNumber = $lineNumber;
        $this->col = $col;
        $this->line = $line;
        $this->offset = $offset;
        $this->selected = $selected;
    }

    public static function fromSourceAndOffset(string $source, int $offset, int $length): OffsetContext
    {
        $lines = explode(PHP_EOL, $source);

        $number = 0;
        $startPosition = 0;

        foreach ($lines as $number => $line) {
            $number = $number + 1;
            $endPosition = $startPosition + strlen($line) + 1;

            if ($offset >= $startPosition && $offset <= $endPosition) {
                $col = $offset - $startPosition;
                $selected = substr($line, $col, $length);

                return new self($offset, $number, $col, $line, $selected);
            }

            $startPosition = $endPosition;
        }

        return new self($offset, $lineNumber, 0, '');
    }

    public function lineNumber(): int
    {
        return $this->lineNumber;
    }

    public function col(): int
    {
        return $this->col;
    }

    public function line(): string
    {
        return $this->line;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function selected(): string
    {
        return $this->selected;
    }
}
