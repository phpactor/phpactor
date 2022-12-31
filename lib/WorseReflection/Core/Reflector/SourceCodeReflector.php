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
     * @param SourceCode|string $sourceCode
     * @param array<string,bool> $visited
     */
    public function reflectClassesIn($sourceCode, array $visited = []): ReflectionClassLikeCollection;

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

    /**
     * @param TextDocument|string $sourceCode
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics($sourceCode): Diagnostics;

    /**
     * @param TextDocument|string $sourceCode
     * @param Offset|ByteOffset|int $offset
     */
    public function reflectNode($sourceCode, $offset): ReflectionNode;

    /**
     * @param TextDocument|string $sourceCode
     */
    public function reflectConstantsIn($sourceCode): ReflectionDeclaredConstantCollection;
}
