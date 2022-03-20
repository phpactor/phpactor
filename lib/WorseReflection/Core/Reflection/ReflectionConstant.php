<?php

namespace Phpactor\WorseReflection\Core\Reflection;

interface ReflectionConstant extends ReflectionMember
{
    /**
     * @return mixed
     */
    public function value();
}
