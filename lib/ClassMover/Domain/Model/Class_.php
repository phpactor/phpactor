<?php

namespace Phpactor\ClassMover\Domain\Model;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;

class Class_
{
    private function __construct(private readonly FullyQualifiedName $name)
    {
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public static function fromFullyQualifiedName(FullyQualifiedName $name): self
    {
        return new self($name);
    }

    public static function fromString(string $name): self
    {
        return self::fromFullyQualifiedName(FullyQualifiedName::fromString($name));
    }
}
