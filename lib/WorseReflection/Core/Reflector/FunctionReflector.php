<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\SourceCode;

interface FunctionReflector
{
    /**
     * @param string|Name $name
     */
    public function reflectFunction($name): ReflectionFunction;

    /**
     * @param string|Name $name
     */
    public function sourceCodeForFunction($name): SourceCode;
}
