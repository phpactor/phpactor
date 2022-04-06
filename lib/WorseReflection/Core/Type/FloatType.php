<?php

namespace Phpactor\WorseReflection\Core\Type;

class FloatType extends ScalarType
{
    public function __toString(): string
    {
        return 'float';
    }
}
