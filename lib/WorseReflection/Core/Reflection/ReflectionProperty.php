<?php

namespace Phpactor\WorseReflection\Core\Reflection;

interface ReflectionProperty extends ReflectionMember
{
    public function isStatic(): bool;

    public function isPromoted(): bool;
}
