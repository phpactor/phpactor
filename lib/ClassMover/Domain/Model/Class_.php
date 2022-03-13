<?php

namespace Phpactor\ClassMover\Domain\Model;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;

class Class_
{
    private FullyQualifiedName $name;

    private function __construct(FullyQualifiedName $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    public static function fromFullyQualifiedName(FullyQualifiedName $name)
    {
        return new self($name);
    }

    public static function fromString(string $name)
    {
        return self::fromFullyQualifiedName(FullyQualifiedName::fromString($name));
    }
}
