<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

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

    public function accepts(Type $type): Trinary
    {
        if (!$type instanceof StringType) {
            return Trinary::false();
        }

        return Trinary::maybe();
    }
}
