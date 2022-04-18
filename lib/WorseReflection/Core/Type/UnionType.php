<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class UnionType implements Type
{
    /**
     * @var Type[]
     */
    public array $types;

    public function __construct(Type ...$types)
    {
        $unique = [];
        foreach ($types as $type) {
            $unique[$type->__toString()] = $type;
        }
        $this->types = array_values($unique);
    }

    public function __toString(): string
    {
        return implode('|', array_map(fn (Type $type) => $type->__toString(), $this->types));
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }

    public function accepts(Type $type): Trinary
    {
        $maybe = false;
        foreach ($this->types as $uType) {
            if ($uType->accepts($type)->isTrue()) {
                return Trinary::true();
            }
            if ($uType->accepts($type)->isMaybe()) {
                $maybe = true;
            }
        }

        if ($maybe) {
            return Trinary::maybe();
        }

        return Trinary::false();
    }

    public function reduce(): Type
    {
        if (count($this->types) === 0) {
            return new MissingType();
        }

        if (count($this->types) === 1) {
            $type = $this->types[array_key_first($this->types)];

            if ($type instanceof ParenthesizedType) {
                return $type->type;
            }

            return $type;
        }

        return $this;
    }

    public function merge(UnionType $type): self
    {
        $types = $this->types;
        foreach ($type->types as $type) {
            $types[] = $type;
        }

        return (new self(...$types))->filter();
    }

    public function narrowTo(Type $narrowTypes): Type
    {
        $narrowTypes = self::toUnion($narrowTypes);

        if (count($narrowTypes->types) === 0) {
            return $this;
        }

        $toRemove = [];
        $toAdd = [];

        // for each of these types
        foreach ($this->types as $type) {
            // check each narrowed to to see if any of these types accept the
            // narrowed version
            foreach ($narrowTypes->types as $narrowType) {
                // if an existing type accepts the narrowed type, remove
                // the existing type
                if ($type->accepts($narrowType)->isTrue() && $type->__toString() !== $narrowType->__toString()) {
                    $toRemove[] = $type;
                    continue;
                }
            }
        }

        return $this->add($narrowTypes)->remove(new self(...$toRemove));
    }

    public function filter(): self
    {
        $types = $this->types;
        $unique = [];

        foreach ($types as $type) {
            if ($type instanceof MissingType) {
                continue;
            }
            if ($type instanceof UnionType) {
                $type = $type->reduce();
            }
            $unique[$type->__toString()] = $type;
        }

        return new self(...array_values($unique));
    }

    public function remove(Type $remove): Type
    {
        $remove = self::toUnion($remove);
        $removeStrings = array_map(fn (Type $t) => $t->__toString(), $remove->types);

        return (new self(...array_filter($this->types, function (Type $type) use ($removeStrings) {
            return !in_array($type->__toString(), $removeStrings);
        })))->reduce();
    }

    public static function toUnion(Type $type): UnionType
    {
        if ($type instanceof NullableType) {
            return self::toUnion($type->type)->add(TypeFactory::null());
        }
        if ($type instanceof UnionType) {
            return $type;
        }

        return new self($type);
    }

    private function add(Type $type): UnionType
    {
        return (new self(...array_merge($this->types, [$type])))->filter();
    }

    public function acceptedBy(Type $accepting): Trinary
    {
        $isMaybe = false;
        foreach ($this->types as $type) {
            $accepted = $accepting->accepts($type);
            if ($accepted->isTrue()) {
                return Trinary::true();
            }
            if ($accepted->isMaybe()) {
                $isMaybe = true;
            }
        }

        if ($isMaybe) {
            return Trinary::maybe();
        }

        return Trinary::false();
    }
}
