<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class ParenthesizedType extends Type
{
    public function __construct(public Type $type)
    {
    }

    public function __toString(): string
    {
        return sprintf('(%s)', $this->type->__toString());
    }

    public function toPhpString(): string
    {
        return $this->type->toPhpString();
    }

    public function accepts(Type $type): Trinary
    {
        return $this->type->accepts($type);
    }

    public function reduce(): Type
    {
        return $this->type;
    }

    /**
     * @return Types<Type>
     */
    public function expandTypes(): Types
    {
        return $this->type->expandTypes();
    }

    public function allTypes(): Types
    {
        return $this->type->allTypes();
    }

    public function map(Closure $mapper): Type
    {
        return new self($this->type->map($mapper));
    }
}
