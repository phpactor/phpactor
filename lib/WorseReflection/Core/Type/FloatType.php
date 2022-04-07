<?php

namespace Phpactor\WorseReflection\Core\Type;

class FloatType extends ScalarType
{
    public function toPhpString(): string
    {
        return 'float';
    }
}
