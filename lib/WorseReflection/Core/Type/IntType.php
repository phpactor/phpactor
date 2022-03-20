<?php

namespace Phpactor\WorseReflection\Core\Type;

final class IntType extends ScalarType
{
    public ?int $value;

    public function __construct(?int $value = null)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return 'int';
    }
}
