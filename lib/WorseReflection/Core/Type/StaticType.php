<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class StaticType extends Type
{
    protected ?Type $class;

    public function __construct(?Type $class = null)
    {
        $this->class = $class;
    }

    public function __toString(): string
    {
        if ($this->class) {
            return sprintf('static(%s)', $this->class->__toString());
        }
        return 'static';
    }

    public function type(): Type
    {
        if ($this->class) {
            return $this->class;
        }

        return TypeFactory::undefined();
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
