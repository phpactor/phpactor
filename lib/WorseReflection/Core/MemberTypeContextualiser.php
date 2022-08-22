<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;

final class MemberTypeContextualiser
{
    public function contextualise(ReflectionClassLike $declaringClass, ReflectionClassLike $class, Type $type): Type
    {
        if ($type instanceof StaticType) {
            return new StaticType($class->type());
        }

        if ($type instanceof SelfType) {
            return new StaticType($declaringClass->type());
        }

        return $class->scope()->resolveFullyQualifiedName($type);
    }
}
