<?php

namespace Phpactor\WorseReflection\Core\Type;

class StringType extends ScalarType
{
    public function toPhpString(): string
    {
        return 'string';
    }
}
