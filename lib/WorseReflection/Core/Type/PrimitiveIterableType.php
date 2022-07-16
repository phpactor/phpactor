<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class PrimitiveIterableType extends Type implements IterableType
{
    public ?Type $valueType;
    public ?Type $keyType;

    public function __construct(?Type $keyType = null, ?Type $valueType = null)
    {
        if (null === $valueType && $keyType) {
            $this->valueType = $keyType;
            $this->keyType = null;
            return;
        }

        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }

    public function __toString(): string
    {
        if ($this->valueType instanceof MissingType) {
            return $this->toPhpString();
        }
        if ($this->keyType === null) {
            return sprintf('iterable<%s>', $this->valueType->__toString());
        }

        return sprintf('iterable<%s,%s>', $this->keyType->__toString(), $this->valueType->__toString());
    }

    public function toPhpString(): string
    {
        return 'iterable';
    }

    public function iterableValueType(): Type
    {
        return $this->valueType ?? new MissingType();
    }

    public function iterableKeyType(): Type
    {
        return $this->keyType ?? new ArrayKeyType();
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof ArrayLiteral) {
            return Trinary::fromBoolean(
                $this->keyType->accepts($type->keyType)->isTrue() && $this->valueType->accepts($type->valueType)->isTrue()
            );
        }
        return Trinary::fromBoolean($type instanceof ArrayType);
    }

    public function toTypes(): Types
    {
        return new Types([$this->valueType]);
    }

    public function map(Closure $mapper): Type
    {
        return new self($this->keyType->map($mapper), $this->valueType->map($mapper));
    }
}
