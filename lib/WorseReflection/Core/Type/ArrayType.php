<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ArrayType implements Type, IterableType
{
    public Type $valueType;

    public Type $keyType;

    public function __construct(Type $keyType = null, ?Type $valueType = null)
    {
        if (null === $valueType) {
            $this->valueType = $keyType;
            $this->keyType = new ArrayKeyType();
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
        if ($this->keyType instanceof ArrayKeyType) {
            return sprintf('%s[]', $this->valueType->__toString());
        }

        return sprintf('array<%s,%s>', $this->keyType->__toString(), $this->valueType->__toString());
    }

    public function toPhpString(): string
    {
        return 'array';
    }

    public function iterableValueType(): Type
    {
        return $this->valueType;
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::fromBoolean($type instanceof ArrayType);
    }
}
