<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;

interface ReflectionFunction extends ReflectionFunctionLike
{
    public function sourceCode(): SourceCode;

    public function name(): Name;
}
