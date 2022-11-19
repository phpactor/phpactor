<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Docblock
{
    private function __construct(private ?string $docblock = null)
    {
    }

    public function __toString()
    {
        return $this->docblock;
    }

    public static function fromString(string $string)
    {
        return new self($string);
    }

    public static function none()
    {
        return new self();
    }

    public function notNone()
    {
        return null !== $this->docblock;
    }

    public function asLines(): array
    {
        $lines = explode(PHP_EOL, $this->docblock);

        return $lines;
    }
}
