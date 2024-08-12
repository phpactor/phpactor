<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

interface CallContext
{
    public function callable(): ReflectionMethod|ReflectionFunction;
    public function arguments(): ?FunctionArguments;
}
