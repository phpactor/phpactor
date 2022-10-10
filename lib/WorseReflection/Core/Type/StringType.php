<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
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

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof ClassStringType) {
            return Trinary::true();
        }
        return parent::accepts($type);
    }
}
