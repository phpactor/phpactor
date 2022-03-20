<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Name;

interface ReflectionFunction extends ReflectionFunctionLike
{
    public function sourceCode(): SourceCode;

    public function name(): Name;
}
