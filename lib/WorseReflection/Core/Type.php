<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Type\MissingType;

abstract class Type
{
    abstract public function __toString(): string;

    abstract public function toPhpString(): string;

    abstract public function accepts(Type $type): Trinary;

    public function isDefined(): bool
    {
        return !$this instanceof MissingType;
    }
}
