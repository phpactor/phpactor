<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;

class BooleanType extends ScalarType
{
    public function __toString(): string
    {
        return 'bool';
    }
}
