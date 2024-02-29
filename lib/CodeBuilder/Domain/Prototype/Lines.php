<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Line>
 */
class Lines extends Collection
{
    public function __toString(): string
    {
        return implode("\n", $this->items);
    }

    /**
     * @param array<Line> $lines
     */
    public static function fromLines(array $lines): self
    {
        return new self($lines);
    }

    protected function singularName(): string
    {
        return 'line';
    }
}
