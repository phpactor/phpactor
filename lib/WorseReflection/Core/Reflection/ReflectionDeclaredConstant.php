<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionDeclaredConstant
{
    public function name(): Name;
    public function type(): Type;
    public function sourceCode(): SourceCode;
}
