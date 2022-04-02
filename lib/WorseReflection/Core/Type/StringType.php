<?php

namespace Phpactor\WorseReflection\Core\Type;

class StringType extends ScalarType
{
    public ?string $value;

    public function __construct(string $value = null)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return 'string';
    }
}
