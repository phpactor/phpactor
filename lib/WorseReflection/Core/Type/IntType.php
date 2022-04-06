<?php

namespace Phpactor\WorseReflection\Core\Type;

final class IntType extends ScalarType
{
    public function __toString(): string
    {
        return 'int';
    }
}
