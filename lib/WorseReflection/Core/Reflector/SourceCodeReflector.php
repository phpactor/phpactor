<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;
use Phpactor\WorseReflection\Core\SourceCode;

interface SourceCodeReflector
{
    /**
     * Reflect all classes (or class-likes) in the given source code.
     *
     * @param SourceCode|string $sourceCode
     * @return ReflectionClassCollection<ReflectionClass>
     */
    public function reflectClassesIn($sourceCode): ReflectionClassCollection;

    /**
     * Reflect all functions in the given source code.
     * @param SourceCode|TextDocument|string $sourceCode
     * @return ReflectionFunctionCollection<ReflectionFunction>
     */
    public function reflectFunctionsIn($sourceCode): ReflectionFunctionCollection;

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     * @param SourceCode|TextDocument|string $sourceCode
     * @param Offset|ByteOffset|int $offset
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset;

    /**
     * @param SourceCode|TextDocument|string $sourceCode
     * @param Offset|ByteOffset|int $offset
     */
    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall;

    /**
     * @param TextDocument|string $sourceCode
     */
    public function navigate($sourceCode): ReflectionNavigation;
}
