<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ArrayKeyType extends ScalarType
{
    public function __toString(): string
    {
        return 'array-key';
    }

    public function toPhpString(): string
    {
        return '';
    }

    public function accepts(Type $type): Trinary
    {
        $parentAccepts = parent::accepts($type);
        if (Trinary::false() !== $parentAccepts) {
            return $parentAccepts;
        }

        if ($type instanceof IntType) {
            return Trinary::true();
        }

        if ($type instanceof FloatType) {
            return Trinary::true();
        }

        if ($type instanceof StringType) {
            return Trinary::true();
        }

        return Trinary::false();
    }
}
