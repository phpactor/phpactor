<?php


namespace Phpactor\TextDocument;

use OutOfRangeException;

class TextEdit
{
    private ByteOffset $start;

    private int $length;

    private string $replacement;

    private function __construct(ByteOffset $start, int $length, string $content)
    {
        if ($length < 0) {
            throw new OutOfRangeException(sprintf(
                'Text edit length cannot be less than 0, got "%s" (start: %s, content: %s)',
                $length,
                $start->toInt(),
                $content
            ));
        }

        $this->start = $start;
        $this->length = $length;
        $this->replacement = $content;
    }

    /**
     * @param int|ByteOffset $start
     */
    public static function create($start, int $length, string $replacement): self
    {
        return new self(ByteOffset::fromIntOrByteOffset($start), $length, $replacement);
    }

    public function end(): ByteOffset
    {
        return $this->start->add($this->length);
    }

    public function range(): ByteOffsetRange
    {
        return ByteOffsetRange::fromByteOffsets($this->start, $this->end());
    }

    public function start(): ByteOffset
    {
        return $this->start;
    }

    public function length(): int
    {
        return $this->length;
    }

    public function replacement(): string
    {
        return $this->replacement;
    }
}
