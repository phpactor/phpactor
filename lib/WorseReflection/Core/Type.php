<?php

namespace Phpactor\WorseReflection\Core;

use Closure;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassNamedType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\Generalizable;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\PrimitiveType;
use Phpactor\WorseReflection\Core\Type\UnionType;

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
     * @return Types<Type&ClassNamedType>
     */
    public function classNamedTypes(): Types
    {
        // @phpstan-ignore-next-line no support for conditional types https://github.com/phpstan/phpstan/issues/3853
        return $this->toTypes()->filter(fn (Type $type) => $type instanceof ClassNamedType);
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

    public function addToUnion(Type $type): UnionType
    {
        if (!$this instanceof UnionType) {
            return new UnionType($this, $type);
        }

        return $this->add($type);
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

    /**
     * If the given type is a literal, return the general type
     */
    public function generalize(): Type
    {
        return $this->map(fn (Type $type) => $type instanceof Generalizable ? $type->generalize() : $type);
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

    /**
     * @param Closure(Type): Type $mapper
     */
    protected function map(Closure $mapper): Type
    {
        return $mapper($this);
    }
}
