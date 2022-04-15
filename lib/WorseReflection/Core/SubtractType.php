<?php

namespace Phpactor\WorseReflection\Core;

class SubtractType implements Type
{
    private Type $from;

    private Type $type;


    public function __construct(Type $from, Type $type)
    {
        $this->from = $from;
        $this->type = $type;
    }

    public function __toString(): string
    {
        return sprintf('%s~%s', $this->from, $this->type);
    }

    public function toPhpString(): string
    {
        return 'mixed';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::false();
    }
}
