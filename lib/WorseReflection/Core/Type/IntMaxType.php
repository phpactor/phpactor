<?php

namespace Phpactor\WorseReflection\Core\Type;

class IntMaxType extends IntType
{
    public function __toString(): string
    {
        return 'max';
    }
}
