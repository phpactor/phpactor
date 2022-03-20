<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\SourceCode;

interface FunctionReflector
{
    public function reflectFunction($name): ReflectionFunction;

    /**
     * @param string|SourceCode $name
     */
    public function sourceCodeForFunction($name): SourceCode;
}
