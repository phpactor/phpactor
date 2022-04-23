<?php

namespace Phpactor\WorseReflection\Core;

abstract class Type
{
    abstract public function __toString(): string;

    abstract public function toPhpString(): string;

    abstract public function accepts(Type $type): Trinary;
}
