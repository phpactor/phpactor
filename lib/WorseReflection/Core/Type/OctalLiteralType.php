<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class OctalLiteralType extends IntType implements Literal, Generalizable
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
        return octdec(substr($this->value, 1));
    }

    public function generalize(): Type
    {
        return new IntType();
    }
}
