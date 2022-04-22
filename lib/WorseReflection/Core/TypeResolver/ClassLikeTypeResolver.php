<?php

namespace Phpactor\WorseReflection\Core\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeResolver;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;

class ClassLikeTypeResolver implements TypeResolver
{
    private ReflectionClassLike $classLike;

    public function __construct(ReflectionClassLike $classLike)
    {
        $this->classLike = $classLike;
    }

    public function resolve(Type $type): Type
    {
        if ($type instanceof StaticType) {
            return $this->classLike->type();
        }
        if ($type instanceof SelfType) {
            return $this->classLike->type();
        }
        return $this->classLike->scope()->resolveFullyQualifiedName($type);
    }
}
