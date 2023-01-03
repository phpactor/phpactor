<?php

namespace Phpactor\WorseReflection\Core\Type;

class IntNegative extends IntType
{
    public function __toString(): string
    {
        return 'negative-int';
    }
    public function toPhpString(): string
    {
        return 'int';
    }
}
