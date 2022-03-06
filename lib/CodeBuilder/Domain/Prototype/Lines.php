<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Lines extends Collection
{
    public function __toString()
    {
        return implode(PHP_EOL, $this->items);
    }
    public static function fromLines(array $lines)
    {
        return new self($lines);
    }

    protected function singularName(): string
    {
        return 'line';
    }
}
