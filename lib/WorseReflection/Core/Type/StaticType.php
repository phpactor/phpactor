<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class StaticType extends Type
{
    final public function __construct(protected ?Type $class = null)
    {
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

    public function map(Closure $mapper): Type
    {
        if (!$this->class) {
            return $mapper($this);
        }
        return $mapper(new static($mapper($this->class)));
    }
}
