<?php

namespace Phpactor\WorseReflection\Core\Type;

final class BinLiteralType extends IntType implements Literal
{
    use LiteralTrait;

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
        return bindec(substr($this->value, 2));
    }
}
