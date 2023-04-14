<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Name;

interface ReflectionFunction extends ReflectionFunctionLike
{
    public function sourceCode(): TextDocument;

    public function name(): Name;
}
