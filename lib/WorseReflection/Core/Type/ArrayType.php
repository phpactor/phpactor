<?php

namespace Phpactor\WorseReflection\Core\Type;


class ArrayType extends PrimitiveIterableType implements IterableType
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
}
