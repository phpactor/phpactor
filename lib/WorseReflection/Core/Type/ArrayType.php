<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Type;

class ArrayType extends PseudoIterableType implements IterableType, HasEmptyType
{
    public function __toString(): string
    {
        if ($this->valueType instanceof MissingType) {
            return $this->toPhpString();
        }
        if ($this->keyType === null) {
            return sprintf('%s[]', $this->valueType->__toString());
        }

        return sprintf('array<%s,%s>', $this->keyType->__toString(), $this->valueType->__toString());
    }

    public function toPhpString(): string
    {
        return 'array';
    }

    public function map(Closure $mapper): Type
    {
        return new self(
            $this->keyType ? $this->iterableKeyType()->map($mapper) : null,
            $this->valueType ? $this->iterableValueType()->map($mapper) : null,
        );
    }

    public function emptyType(): Type
    {
        return new ArrayLiteral([]);
    }
}
