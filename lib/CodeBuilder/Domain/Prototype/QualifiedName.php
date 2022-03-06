<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class QualifiedName
{
    private $name;

    protected function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromString(string $name): QualifiedName
    {
        return new static($name);
    }
}
