<?php

namespace Phpactor\WorseReflection\Core\Type;

class IntType extends NumericType
{
    public function toPhpString(): string
    {
        return 'int';
    }
}
