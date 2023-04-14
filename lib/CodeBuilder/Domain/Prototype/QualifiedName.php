<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class QualifiedName
{
    protected function __construct(private string $name)
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public static function fromString(string $name): QualifiedName
    {
        return new static($name);
    }
}
