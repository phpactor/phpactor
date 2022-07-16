<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class FloatType extends NumericType implements HasEmptyType
{
    public function toPhpString(): string
    {
        return 'float';
    }

    public function emptyType(): Type
    {
        return new FloatLiteralType(0.0);
    }
}
