<?php

namespace Phpactor\WorseReflection\Core\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeResolver;

class DefaultTypeResolver implements TypeResolver
{
    private ReflectionScope $scope;

    public function __construct(ReflectionScope $scope)
    {
        $this->scope = $scope;
    }

    public function resolve(Type $type): Type
    {
        return $this->scope->resolveFullyQualifiedName($type);
    }
}
