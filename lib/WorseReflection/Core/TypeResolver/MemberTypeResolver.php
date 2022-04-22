<?php

namespace Phpactor\WorseReflection\Core\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeResolver;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;

class MemberTypeResolver implements TypeResolver
{
    private ReflectionMember $member;

    public function __construct(ReflectionMember $method)
    {
        $this->member = $method;
    }

    public function resolve(Type $type): Type
    {
        if ($type instanceof SelfType) {
            return $this->member->declaringClass()->type();
        }
        if ($type instanceof StaticType) {
            return $this->member->class()->type();
        }

        return $this->member->scope()->resolveFullyQualifiedName($type);
    }
}
