<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Type\ConditionalType;

class ConditionalTypeResolver
{
    public function resolve(Type $type, ReflectionParameterCollection $reflectionParameterCollection): Type
    {
        if (!$type instanceof ConditionalType) {
            return $type;
        }
    }
}
