<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class BooleanLiteralType extends BooleanType implements Literal, Generalizable
{
    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    public function value()
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new BooleanType();
    }
}
