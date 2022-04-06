<?php

namespace Phpactor\WorseReflection\Core\Type;

final class OctalLiteralType extends IntType implements Literal
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function value()
    {
        return octdec(substr($this->value, 1));
    }
}
