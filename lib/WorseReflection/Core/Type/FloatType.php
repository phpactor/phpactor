<?php

namespace Phpactor\WorseReflection\Core\Type;

class FloatType extends NumericType
{
    public function toPhpString(): string
    {
        return 'float';
    }
}
