<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ExcludeType implements Type
{
    public Type $type;

    public Type $exclude;


    public function __construct(Type $type, Type $exclude)
    {
        $this->type = $type;
        $this->exclude = $exclude;
    }

    public function __toString(): string
    {
        return sprintf('exclude<%s, %s>', $this->type->__toString(), $this->exclude->__toString());
    }

    public function toPhpString(): string
    {
        return $this->type->toPhpString();
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }
}
