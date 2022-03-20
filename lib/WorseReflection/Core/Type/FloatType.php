<?php

namespace Phpactor\WorseReflection\Core\Type;

final class FloatType extends ScalarType
{
    public ?float $value;

    public function __construct(?float $value = null)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return 'float';
    }
}
