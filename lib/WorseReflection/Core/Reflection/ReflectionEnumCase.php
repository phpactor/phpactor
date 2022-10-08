<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Type;

interface ReflectionEnumCase extends ReflectionMember
{
    public function value(): Type;
}
