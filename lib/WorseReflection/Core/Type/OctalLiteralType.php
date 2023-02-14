<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class OctalLiteralType extends IntType implements Literal, Generalizable
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
        return octdec(substr($this->value, 1));
    }

    public function generalize(): Type
    {
        return new IntType();
    }
}
