<?php

namespace Phpactor\WorseReflection\Core\Type;

class IntType extends ScalarType
{
    public function __toString(): string
    {
        return 'int';
    }
}
