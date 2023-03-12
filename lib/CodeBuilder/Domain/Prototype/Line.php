<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Line
{
    private function __construct(private string $line)
    {
    }

    public function __toString(): string
    {
        return $this->line;
    }

    public static function fromString(string $line): Line
    {
        return new self($line);
    }
}
