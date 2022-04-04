<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class UnionType implements Type
{
    /**
     * @var Type[]
     */
    public array $types;

    public function __construct(Type ...$types)
    {
        $this->types = $types;
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
            return $this->types[array_key_first($this->types)];
        }

        return $this;
    }
}
