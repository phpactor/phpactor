<?php

namespace Phpactor\WorseReflection\Core\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeResolver;

class ClassLikeTypeResolver implements TypeResolver
{
    private ReflectionClassLike $classLike;

    public function __construct(ReflectionClassLike $classLike)
    {
        $this->classLike = $classLike;
    }

    public function resolve(Type $type): Type
    {
        return $this->classLike->scope()->resolveFullyQualifiedName($type);
    }
}
