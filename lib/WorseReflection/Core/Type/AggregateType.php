<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;

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
            if ($type instanceof AggregateType && $type instanceof $this) {
                foreach ($type->types as $utype) {
                    $unique[$utype->__toString()] = $utype;
                }
                continue;
            }
            if ($type instanceof AggregateType && count($type->types) > 1) {
                $type = TypeFactory::parenthesized($type);
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

        $remove = [];
        foreach ($this->types as $type1) {
            foreach ($this->types as $type2) {
                if ($type1 === $type2) {
                    continue;
                }

                if ($type1->consumes($type2)->isTrue()) {
                    $remove[] = $type2;
                }
            }
        }

        $type = $this;
        foreach ($remove as $removeType) {
            $type = $this->remove($removeType);
        }

        return $type;
    }

    abstract public function withTypes(Type ...$types): AggregateType;

    public function clean(): AggregateType
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

        return $this->withTypes(...array_values($unique));
    }

    public function remove(Type $remove): Type
    {
        $remove = UnionType::toUnion($remove);
        $removeStrings = array_map(fn (Type $t) => $t->__toString(), $remove->types);

        return ($this->withTypes(...array_filter($this->types, function (Type $type) use ($removeStrings) {
            return !in_array($type->__toString(), $removeStrings);
        })))->reduce();
    }

    public function expandTypes(): Types
    {
        $types = new Types([]);
        foreach ($this->types as $type) {
            $types = $types->merge($type->expandTypes());
        }
        return $types;
    }

    public function allTypes(): Types
    {
        $types = new Types([]);
        foreach ($this->expandTypes() as $type) {
            $types = $type->merge($type->allTypes());
        }

        return $types;
    }

    public function add(Type $type): AggregateType
    {
        return ($this->withTypes(...array_merge($this->types, [$type])))->clean();
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
        return ($this->withTypes(...array_filter($this->types, function (Type $type) {
            return !$type instanceof NullType;
        })))->reduce();
    }

    public function map(Closure $mapper): Type
    {
        return $this->withTypes(...array_map($mapper, $this->types));
    }

    public function filter(Closure $closure): AggregateType
    {
        return $this->withTypes(...array_filter($this->types, $closure));
    }
    public function count(): int
    {
        return count($this->types);
    }

    public function contains(Type $narrowTo): bool
    {
        foreach ($this->types as $type) {
            if ($type->equals($narrowTo)) {
                return true;
            }
        }

        return false;
    }
}
