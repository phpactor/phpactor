<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @extends MemberAccessContext<ReflectionMethod>
 */
class MethodCallContext extends MemberAccessContext implements CallContext
{
    public function callable(): ReflectionMethod|ReflectionFunction
    {
        return $this->member;
    }
}
