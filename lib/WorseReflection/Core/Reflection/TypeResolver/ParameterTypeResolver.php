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

    public function resolve(): Types
    {
        $docblock = $this->parameter->method()->docblock();
        $docblockTypes = $docblock->parameterTypes($this->parameter->name());

        $resolvedTypes = array_map(function (Type $type) {
            return $this->parameter->scope()->resolveFullyQualifiedName($type);
        }, iterator_to_array($docblockTypes));

        if (count($resolvedTypes)) {
            return Types::fromTypes($resolvedTypes);
        }

        if (!$this->parameter->type() instanceof MissingType) {
            return Types::fromTypes([ $this->parameter->type() ]);
        }

        return Types::empty();
    }
}
