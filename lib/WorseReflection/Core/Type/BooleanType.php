<?php

namespace Phpactor\WorseReflection\Core\Type;

class BooleanType extends ScalarType
{
    public function toPhpString(): string
    {
        return 'bool';
    }
}
