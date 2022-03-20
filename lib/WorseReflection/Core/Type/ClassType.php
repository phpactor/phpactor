<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ClassType implements Type
{
    public ClassName $name;

    public function __construct(ClassName $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name->full();
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }

    public function name(): ClassName
    {
        return $this->name;
    }

    public function is(Type $type): Trinary
    {
        if ($type instanceof MissingType) {
            return Trinary::maybe();
        }

        if (!$type instanceof ClassType) {
            return Trinary::false();
        }

        return Trinary::fromBoolean($type->name() == $this->name());
    }

    public function accepts(Type $type): Trinary
    {
        if ($this->is($type)->isTrue()) {
            return Trinary::true();
        }

        if ($type instanceof ClassType) {
            return Trinary::maybe();
        }

        return Trinary::false();
    }
}
