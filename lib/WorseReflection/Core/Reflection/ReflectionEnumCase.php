<?php

namespace Phpactor\WorseReflection\Core\Reflection;

interface ReflectionEnumCase extends ReflectionMember
{
    /**
     * @return mixed
     */
    public function value();
}
