<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class UnionType implements Type
{
    /**
     * @var Type[]
     */
    public array $types = [];

    public function __construct(Type ...$types)
    {
        foreach ($types as $type) {
            $this->types[$type->__toString()] = $type;
        }
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

    public function exclude(Type $exclude): self
    {
        return new self(...array_filter($this->types, function (Type $t) use ($exclude) {
            return $t->__toString() !== $exclude->__toString();
        }));
    }

    public function anihilate(): self
    {
        $anihilated = clone $this;

        foreach ($anihilated->typesByClass(UnionType::class) as $union) {
            $anihilated->replaceType($union, $union->anihilate());
        }

        foreach ($anihilated->typesByClass(NotType::class) as $notType) {
            $anihilated = $anihilated->exclude($notType->not)->exclude($notType);
        }
        foreach ($anihilated->typesByClass(MissingType::class) as $missing) {
            $anihilated = $anihilated->exclude($missing);
        }

        return $anihilated;
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T[]
     */
    private function typesByClass(string $class): array
    {
        return array_filter($this->types, fn (Type $type) => $type instanceof $class);
    }

    private function replaceType(Type $search, Type $replace): void
    {
        foreach ($this->types as $i => $type) {
            if ($search === $type) {
                $this->types[$i] = $replace;
            }
        }
    }
}
