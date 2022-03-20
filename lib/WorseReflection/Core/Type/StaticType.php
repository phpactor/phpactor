<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class StaticType implements Type
{
    public function __toString(): string
    {
        return 'static';
    }

    public function toPhpString(): string
    {
        return 'static';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }
}
