<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;

/**
 * @template T of AggregateType
 */
abstract class AggregateType extends Type
{
    /**
     * @var Type[]
     */
    public array $types;

    public function __construct(Type ...$types)
    {
        $unique = [];
        $toMerge = [];
        $hasNull = false;
        foreach ($types as $type) {
            if ($type instanceof AggregateType) {
                foreach ($type->types as $utype) {
                    $unique[$utype->__toString()] = $utype;
                }
                continue;
            }
            if ($type instanceof NullableType) {
                $type = $type->type;
                $null = TypeFactory::null();
                $unique[$null->__toString()] = $null;
            }

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
        return implode('|', array_map(fn (Type $type) => $type->toPhpString(), $this->types));
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

        if (count($this->types) === 2 && $this->isNullable()) {
            return TypeFactory::nullable($this->stripNullable());
        }

        return $this;
    }

    public function narrowTo(Type $narrowTypes): Type
    {
        $narrowTypes = UnionType::toUnion($narrowTypes);

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

        return $this->add($narrowTypes)->remove($this->new(...$toRemove));
    }

    /**
     * @return T
     */
    abstract protected function new(Type ...$types): AggregateType;

    public function filter(): self
    {
        $types = $this->types;
        $unique = [];

        foreach ($types as $type) {
            if ($type instanceof MissingType) {
                continue;
            }
            if ($type instanceof AggregateType) {
                $type = $type->reduce();
            }
            $unique[$type->__toString()] = $type;
        }

        return $this->new(...array_values($unique));
    }

    public function remove(Type $remove): Type
    {
        $remove = UnionType::toUnion($remove);
        $removeStrings = array_map(fn (Type $t) => $t->__toString(), $remove->types);

        return ($this->new(...array_filter($this->types, function (Type $type) use ($removeStrings) {
            return !in_array($type->__toString(), $removeStrings);
        })))->reduce();
    }

    public function toTypes(): Types
    {
        return new Types($this->types);
    }

    /**
     * @return T
     */
    public function add(Type $type): AggregateType
    {
        return ($this->new(...array_merge($this->types, [$type])))->filter();
    }

    public function isNull(): bool
    {
        $reduced = $this->reduce();
        return $reduced instanceof NullType;
    }

    public function isNullable(): bool
    {
        foreach ($this->types as $type) {
            if ($type->isNull()) {
                return true;
            }
        }

        return false;
    }

    public function stripNullable(): Type
    {
        return ($this->new(...array_filter($this->types, function (Type $type) {
            return !$type instanceof NullType;
        })))->reduce();
    }

    protected function map(Closure $mapper): Type
    {
        return $this->new(...array_map($mapper, $this->types));
    }
}
