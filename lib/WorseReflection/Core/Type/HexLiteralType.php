<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class HexLiteralType extends IntType implements Literal, Generalizable
{
    use LiteralTrait;

    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function value(): int|float
    {
        return hexdec(substr($this->value, 2));
    }

    public function generalize(): Type
    {
        return new IntType();
    }
}
