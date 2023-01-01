<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class PseudoIterableType extends Type implements IterableType
{
    protected ?Type $valueType;

    protected ?Type $keyType;

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
        if ($this->valueType === null) {
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
                $this->iterableKeyType()->accepts($type->keyType)->isTrue() && $this->iterableValueType()->accepts($type->valueType)->isTrue()
            );
        }
        return Trinary::fromBoolean($type instanceof ArrayType);
    }

    public function expandTypes(): Types
    {
        return new Types([$this->iterableValueType()]);
    }

    public function allTypes(): Types
    {
        $types = new Types([]);
        foreach ($this->expandTypes() as $type) {
            $types = $types->merge($type->allTypes());
        }

        return $types;
    }

    public function map(Closure $mapper): Type
    {
        return new self(
            $this->keyType ? $this->iterableKeyType()->map($mapper) : null,
            $this->valueType ? $this->iterableValueType()->map($mapper) : null,
        );
    }

    /**
     * DANGEROUS: @see Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter
     */
    public function setValueType(Type $type): void
    {
        $this->valueType = $type;
    }
}
