<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class NeverType extends Type
{
    public function __toString(): string
    {
        return 'never';
    }

    public function toPhpString(): string
    {
        return 'never';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::false();
    }
}
