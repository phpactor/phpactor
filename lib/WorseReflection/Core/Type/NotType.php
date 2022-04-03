<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class NotType implements Type
{
    public Type $not;

    public function __construct(Type $not)
    {
        $this->not = $not;
    }

    public function __toString(): string
    {
        return sprintf('not<%s>', $this->not->__toString());
    }

    public function toPhpString(): string
    {
        return '';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::fromBoolean($type->__toString() !== $this->not->__toString());
    }
}
