<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\TypeUtil;

class ParameterTypeResolver
{
    private ReflectionParameter $parameter;

    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function resolve(): Type
    {
        $docblock = $this->parameter->method()->docblock();
        $docblockType = $docblock->parameterType($this->parameter->name());

        return TypeUtil::firstDefined($docblockType, $this->parameter->type());
    }
}
