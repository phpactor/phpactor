<?php

namespace Phpactor\WorseReflection\Core\Type;

class IntPositive extends IntType
{
    public function __toString(): string
    {
        return 'positive-int';
    }
    public function toPhpString(): string
    {
        return 'int';
    }
}
