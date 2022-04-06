<?php

namespace Phpactor\WorseReflection\Core\Type;

final class FloatType extends ScalarType
{
    public function __toString(): string
    {
        return 'float';
    }
}
