<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;

final class BooleanLiteralType extends BooleanType implements  Literal
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
}
