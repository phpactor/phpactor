<?php

namespace Phpactor\WorseReflection\Core;

use Closure;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\Generalizable;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\PrimitiveType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Type\VoidType;

abstract class Type
{
    abstract public function __toString(): string;

    abstract public function toPhpString(): string;

    /**
     * As in a parameter can accept an argument.
     *
     * - string         < string
     * - string|null    < null || string
     * - "hello"|string < hello || string
     * - string         < "hello" or any string literal (which narrow the string type)
     * - "hello"        < does not accept string
     * - ""             - does not accept string
     */
    abstract public function accepts(Type $type): Trinary;

    /**
     * Return a collection of first-class types.
     *
     * If this type is an aggregate this method will
     * return a collection types of that aggregate or intersection type.
     *
     * @return Types<Type>
     */
    public function expandTypes(): Types
    {
        return new Types([$this]);
    }

    /**
     * Return ALL types referenced in this type.
     *
     * For example:
     *
     * - `MyGeneric<One,string,int>`: Will Return `MyGeneric`, `One`, `string` and `int`.
     * - `MyClass`: Will return `MyClass`.
     * - `Closure(Foobar,int): float`: Will return `Closure` (as a "class" type), `Foobar`, `int` and `float` `
     * @return Types<list<Type>>
     */
    public function allTypes(): Types
    {
        return new Types([$this]);
    }


    public function isDefined(): bool
    {
        return !$this instanceof MissingType;
    }

    public function isVoid(): bool
    {
        return $this instanceof VoidType;
    }

    public function isClass(): bool
    {
        return $this instanceof ClassType;
    }

    public function isClosure(): bool
    {
        return $this instanceof ClosureType;
    }

    public function isArray(): bool
    {
        return $this instanceof ArrayType;
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function addType(Type $type): AggregateType
    {
        return new UnionType($this, $type);
    }

    public function isPrimitive(): bool
    {
        return $this instanceof PrimitiveType;
    }

    public function short(): string
    {
        $type = $this;
        if ($type instanceof AggregateType) {
            // generalize literal types in order to de-duplicate them
            $type = $type->generalize()->reduce();
        }

        if ($type instanceof UnionType) {
            return implode('|', array_map(fn (Type $t) => $t->short(), $type->types));
        }

        if ($type instanceof IntersectionType) {
            return implode('&', array_map(fn (Type $t) => $t->short(), $type->types));
        }

        if ($type instanceof NullableType) {
            return '?' . $type->type->short();
        }

        if ($type instanceof ClassType) {
            return $type->name()->short();
        }

        return $type->toPhpString();
    }

    /**
     * @returnc self
     */
    public function toLocalType(ReflectionScope $scope): self
    {
        // TODO: do not modify type by reference
        return $this->map(fn (Type $type) => $scope->resolveLocalType(clone $type));
    }

    public static function fromTypes(Type ...$types): Type
    {
        if (count($types) === 0) {
            return new MissingType();
        }
        if (count($types) === 1) {
            return $types[0];
        }

        return new UnionType(...$types);
    }

    public function generalize(): Type
    {
        return $this->map(function (Type $type) {
            return $type instanceof Generalizable ? $type->generalize() : $type;
        });
    }

    public function equals(Type $type): bool
    {
        return $this->__toString() === $type->__toString();
    }

    public function instanceof(Type $type): Trinary
    {
        return Trinary::fromBoolean($type->equals($this));
    }

    public function isNull(): bool
    {
        return false;
    }

    public function stripNullable(): Type
    {
        return $this;
    }

    public function reduce(): Type
    {
        return $this;
    }

    public function isTrue(): bool
    {
        return false;
    }

    public function isEmpty(): Trinary
    {
        $empty = TypeFactory::unionEmpty()->accepts($this);

        if ($empty->isTrue() || $empty->isFalse()) {
            return $empty;
        }

        if ($this instanceof Literal) {
            return Trinary::false();
        }
        return Trinary::maybe();
    }

    public function isMixed(): bool
    {
        return $this instanceof MixedType;
    }
    public function mergeType(Type $type): Type
    {
        if ($this instanceof MissingType) {
            return $type;
        }

        if ($this instanceof AggregateType) {
            return $this->add($type);
        }

        return TypeFactory::intersection($this, $type);
    }

    /**
     * @param Closure(Type): Type $mapper
     */
    public function map(Closure $mapper): Type
    {
        return $mapper($this);
    }

    /**
     * If this type can "consume" or replace the given type
     */
    public function consumes(Type $type2): Trinary
    {
        return Trinary::maybe();
    }
}
