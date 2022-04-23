<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class SelfType extends Type
{
    public function __toString(): string
    {
        return 'self';
    }

    public function toPhpString(): string
    {
        return 'self';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }
}
