<?php

namespace Phpactor\WorseReflection\Core\Type;

class StringLiteralType extends StringType implements Literal
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
}
