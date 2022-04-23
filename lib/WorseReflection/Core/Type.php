<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\PrimitiveType;
use Phpactor\WorseReflection\Core\Type\UnionType;

abstract class Type
{
    abstract public function __toString(): string;

    abstract public function toPhpString(): string;

    abstract public function accepts(Type $type): Trinary;

    /**
     * @return Types<ClassType>
     */
    public function classTypes(): Types
    {
        // @phpstan-ignore-next-line no support for conditional types https://github.com/phpstan/phpstan/issues/3853
        return $this->toTypes()->filter(fn (Type $type) => $type instanceof ClassType);
    }

    /**
     * @return Types<Type>
     */
    public function toTypes(): Types
    {
        return new Types([$this]);
    }


    public function isDefined(): bool
    {
        return !$this instanceof MissingType;
    }

    public function isClass(): bool
    {
        return $this instanceof ClassType;
    }

    public function isNullable(): bool
    {
        return $this instanceof NullableType;
    }

    public function isPrimitive(): bool
    {
        return $this instanceof PrimitiveType;
    }

    public function short(): string
    {
        $type = $this;
        if ($type instanceof UnionType) {
            $type = $type->reduce();
        }

        if ($type instanceof UnionType) {
            return implode('|', array_map(fn (Type $t) => $t->short(), $type->types));
        }

        if ($type instanceof NullableType) {
            return '?' . $type->type->short();
        }

        if ($type instanceof ClassType) {
            return $type->name()->short();
        }

        return $type->toPhpString();
    }
}
