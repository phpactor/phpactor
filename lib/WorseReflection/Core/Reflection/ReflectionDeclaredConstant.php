<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Type;

interface ReflectionDeclaredConstant
{
    public function name(): string;
    public function type(): Type;
}
