<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

interface ClassInvocation
{
    public function class(): ReflectionClassLike;
    public function arguments(): ReflectionArgumentCollection;
}
