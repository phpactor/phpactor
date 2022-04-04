<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

class VirtualReflectionProperty extends VirtualReflectionMember implements ReflectionProperty
{
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
