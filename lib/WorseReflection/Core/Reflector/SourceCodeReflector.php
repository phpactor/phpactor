<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;
use Phpactor\WorseReflection\Core\SourceCode;

interface SourceCodeReflector
{
    /**
     * Reflect all classes (or class-likes) in the given source code.
     *
     * @param array<string,bool> $visited
     */
    public function reflectClassesIn(
        SourceCode|TextDocument|string $sourceCode,
        array $visited = []
    ): ReflectionClassLikeCollection;

    /**
     * Reflect all functions in the given source code.
     *
     * @return ReflectionFunctionCollection<ReflectionFunction>
     */
    public function reflectFunctionsIn(SourceCode|TextDocument|string $sourceCode): ReflectionFunctionCollection;

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     */
    public function reflectOffset(
        SourceCode|TextDocument|string $sourceCode,
        Offset|ByteOffset|int $offset
    ): ReflectionOffset;

    public function reflectMethodCall(
        SourceCode|TextDocument|string $sourceCode,
        Offset|ByteOffset|int $offset
    ): ReflectionMethodCall;

    public function navigate(SourceCode|TextDocument|string $sourceCode): ReflectionNavigation;

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(SourceCode|TextDocument|string $sourceCode): Diagnostics;

    public function reflectNode(
        SourceCode|TextDocument|string $sourceCode,
        Offset|ByteOffset|int $offset
    ): ReflectionNode;

    public function reflectConstantsIn(
        SourceCode|TextDocument|string $sourceCode
    ): ReflectionDeclaredConstantCollection;
}
