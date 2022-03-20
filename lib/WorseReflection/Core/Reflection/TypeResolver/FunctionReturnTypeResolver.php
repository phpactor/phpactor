<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;

class FunctionReturnTypeResolver
{
    private ReflectionFunction $function;

    public function __construct(ReflectionFunction $function)
    {
        $this->function = $function;
    }

    public function resolve(): Types
    {
        $resolvedTypes = $this->getDocblockTypesFromFunction($this->function);

        if (!$this->function->type() instanceof MissingType) {
            $resolvedTypes = $resolvedTypes->merge(Types::fromTypes([ $this->function->type() ]));
        }

        return $resolvedTypes;
    }

    private function getDocblockTypesFromFunction(ReflectionFunction $function): Types
    {
        return $this->resolveTypes(iterator_to_array($function->docblock()->returnTypes()));
    }

    private function resolveTypes(array $types): Types
    {
        return Types::fromTypes(array_map(function (Type $type) {
            return $this->function->scope()->resolveFullyQualifiedName($type);
        }, $types));
    }
}
