<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;

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
        $docblockTypes = $docblock->parameterTypes($this->parameter->name());

        $resolvedTypes = array_map(function (Type $type) {
            return $this->parameter->scope()->resolveFullyQualifiedName($type);
        }, iterator_to_array($docblockTypes));

        foreach ($resolvedTypes as $type) {
            return $type;
        }

        return $this->parameter->type();
    }
}
