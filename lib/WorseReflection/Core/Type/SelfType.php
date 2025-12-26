<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class SelfType extends Type
{
    public function __construct(private readonly ?Type $class = null)
    {
    }

    public function __toString(): string
    {
        if ($this->class) {
            return sprintf('self(%s)', $this->class->__toString());
        }
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

    public function type(): Type
    {
        if ($this->class) {
            return $this->class;
        }

        return TypeFactory::undefined();
    }
}
