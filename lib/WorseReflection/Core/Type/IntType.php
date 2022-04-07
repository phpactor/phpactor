<?php

namespace Phpactor\WorseReflection\Core\Type;

class IntType extends ScalarType
{
    public function toPhpString(): string
    {
        return 'int';
    }
}
