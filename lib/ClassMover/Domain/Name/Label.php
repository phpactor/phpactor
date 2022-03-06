<?php

namespace Phpactor\ClassMover\Domain\Name;

class Label
{
    private $label;

    private function __construct($label)
    {
        $this->label = $label;
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
