<?php

namespace Phpactor\WorseReflection\Core\Type;

final class BinLiteralType extends IntType implements Literal
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
        return bindec(substr($this->value, 2));
    }
}
