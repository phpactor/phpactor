<?php

namespace Phpactor\WorseReflection\Core\Type;

class ClassStringType extends StringType
{
    public function __toString(): string
    {
        return 'class-string';
    }

    public function toPhpString(): string
    {
        return 'string';
    }
}
