<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

class VirtualReflectionProperty extends VirtualReflectionMember implements ReflectionProperty
{
    public static function fromReflectionProperty(ReflectionProperty $reflectionProperty): self
    {
        return new self(
            $reflectionProperty->position(),
            $reflectionProperty->declaringClass(),
            $reflectionProperty->class(),
            $reflectionProperty->name(),
            $reflectionProperty->frame(),
            $reflectionProperty->docblock(),
            $reflectionProperty->scope(),
            $reflectionProperty->visibility(),
            $reflectionProperty->inferredType(),
            $reflectionProperty->type(),
            $reflectionProperty->deprecation()
        );
    }

    public function isVirtual(): bool
    {
        return true;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_PROPERTY;
    }

    public function isPromoted(): bool
    {
        return false;
    }
}
