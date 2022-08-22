<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class ThisType extends StaticType
{
    public function __toString(): string
    {
        if ($this->class) {
            return sprintf('$this(%s)', $this->class->__toString());
        }
        return '$this';
    }
}
