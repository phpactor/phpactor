<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class StringType extends ScalarType implements HasEmptyType
{
    public function toPhpString(): string
    {
        return 'string';
    }

    public function emptyType(): Type
    {
        return new StringLiteralType('');
    }
}
