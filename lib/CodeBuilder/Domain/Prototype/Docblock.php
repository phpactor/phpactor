<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Docblock
{
    private function __construct(private ?string $docblock = null)
    {
    }

    public function __toString(): string
    {
        return $this->docblock ?? '';
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    public static function none(): self
    {
        return new self();
    }

    public function notNone(): bool
    {
        return null !== $this->docblock;
    }

    /**
     * @return list<string>
     */
    public function asLines(): array
    {
        if ($this->docblock === null) {
            return [];
        }

        return explode("\n", $this->docblock);
    }
}
