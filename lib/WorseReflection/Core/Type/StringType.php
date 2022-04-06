<?php

namespace Phpactor\WorseReflection\Core\Type;

class StringType extends ScalarType
{
    public function __toString(): string
    {
        return 'string';
    }
}
