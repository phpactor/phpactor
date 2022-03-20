<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;

interface SourceCodeReflector
{
    /**
     * Reflect all classes (or class-likes) in the given source code.
     *
     * @return ReflectionClassCollection<ReflectionClass>
     */
    public function reflectClassesIn($sourceCode): ReflectionClassCollection;

    /**
     * Reflect all functions in the given source code.
     * @return ReflectionFunctionCollection<ReflectionFunction>
     */
    public function reflectFunctionsIn($sourceCode): ReflectionFunctionCollection;

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset;

    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall;
}
