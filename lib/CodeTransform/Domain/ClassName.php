<?php

namespace Phpactor\CodeTransform\Domain;

use InvalidArgumentException;

final class ClassName
{
    private $name;

    private function __construct(string $name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException(
                'Class name cannot be empty'
            );
        }
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function namespace(): string
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return '';
        }

        return substr($this->name, 0, $pos ?: 0);
    }

    public function short(): string
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return $this->name;
        }

        return substr($this->name, $pos + 1);
    }
}
