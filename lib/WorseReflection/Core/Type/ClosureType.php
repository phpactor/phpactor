<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class ClosureType extends CallableType
{
    public function __toString(): string
    {
        return sprintf(
            'Closure(%s): %s',
            implode(',', array_map(fn (Type $type) => $type->__toString(), $this->args)),
            $this->returnType->__toString()
        );
    }
}
