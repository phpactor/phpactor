<?php

namespace Phpactor\WorseReflection\Core\Type;

class BooleanType extends ScalarType
{
    public function toPhpString(): string
    {
        return 'bool';
    }

    public function or(BooleanType $right): BooleanType
    {
        return new BooleanType();
    }

    public function and(BooleanType $booleanType): BooleanType
    {
        return new BooleanType();
    }
}
