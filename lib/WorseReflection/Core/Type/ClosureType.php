<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;

class ClosureType extends CallableType implements ClassNamedType
{
    public function __toString(): string
    {
        return sprintf(
            'Closure(%s): %s',
            implode(',', array_map(fn (Type $type) => $type->__toString(), $this->args)),
            $this->returnType->__toString()
        );
    }

    public function toPhpString(): string
    {
        return 'Closure';
    }

    public function name(): ClassName
    {
        return ClassName::fromString('Closure');
    }
}
