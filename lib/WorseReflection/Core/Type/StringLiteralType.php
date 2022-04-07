<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class StringLiteralType extends StringType implements Literal, Generalizable
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return sprintf('"%s"', $this->value);
    }

    
    public function value()
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new StringType();
    }
}
