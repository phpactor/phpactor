<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Type;

interface ReflectionMethod extends ReflectionMember, ReflectionFunctionLike
{
    /**
     * @deprecated - use type()
     */
    public function returnType(): Type;

    public function isAbstract(): bool;

    public function isStatic(): bool;
}
