<?php

namespace Phpactor\Diff;

use SebastianBergmann\Diff\Chunk;
use SebastianBergmann\Diff\Line;

class DiffLinesConsumer
{
    private int $origLine;

    private int $position = 0;

    public function __construct(
        private Chunk $chunk
    ) {
        $this->origLine = $chunk->getStart();
    }

    public function getOrigLine(): int
    {
        return $this->origLine;
    }

    public function eat(): ?Line
    {
        if ($this->position >= count($this->chunk->getLines())) {
            return null;
        }

        $line = $this->chunk->getLines()[$this->position++];

        if (in_array($line->getType(), [Line::REMOVED, Line::UNCHANGED])) {
            $this->origLine++;
        }

        return $line;
    }

    /**
     * @return Line[]|null
     */
    public function eatWhileType(int $type): ?array
    {
        $lines = [];

        while (($line = $this->eat()) && $line->getType() === $type) {
            $lines[] = $line;
        }

        if ($line && $line->getType() !== $type) {
            $this->rewind();
        }

        if (count($lines) === 0) {
            return null;
        }

        return $lines;
    }

    /**
     * @return Line[]|null
     */
    public function eatUnchanged(): ?array
    {
        return $this->eatWhileType(Line::UNCHANGED);
    }

    /**
     * @return Line[]|null
     */
    public function eatRemoved(): ?array
    {
        return $this->eatWhileType(Line::REMOVED);
    }

    /**
     * @return Line[]|null
     */
    public function eatAdded(): ?array
    {
        return $this->eatWhileType(Line::ADDED);
    }

    public function current(): ?Line
    {
        return $this->chunk->getLines()[$this->position] ?? null;
    }

    private function rewind(): void
    {
        if ($this->position === 0) {
            return;
        };

        $this->position--;
        $line = $this->current();

        if ($line && in_array($line->getType(), [Line::REMOVED, Line::UNCHANGED])) {
            $this->origLine--;
        }
    }
}
