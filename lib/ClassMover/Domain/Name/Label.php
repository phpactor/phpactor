<?php

namespace Phpactor\ClassMover\Domain\Name;

class Label
{
    private function __construct(private $label)
    {
    }

    public function __toString()
    {
        return $this->label;
    }

    public static function fromString(string $label): Label
    {
        return new static($label);
    }
}
