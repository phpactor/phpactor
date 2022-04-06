<?php

namespace Phpactor\WorseReflection\Core\Type;

final class HexLiteralType extends IntType implements Literal
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
        return hexdec(substr($this->value, 2));
    }
}
