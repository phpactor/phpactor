<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;
use Phpactor\TextDocument\TextDocument;

interface SourceCodeReflector
{
    /**
     * Reflect all classes (or class-likes) in the given source code.
     *
     * @param array<string,bool> $visited
     */
    public function reflectClassesIn(
        TextDocument|TextDocument|string $sourceCode,
        array $visited = []
    ): ReflectionClassLikeCollection;

    /**
     * Reflect all functions in the given source code.
     *
     * @return ReflectionFunctionCollection<ReflectionFunction>
     */
    public function reflectFunctionsIn(TextDocument|TextDocument|string $sourceCode): ReflectionFunctionCollection;

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     */
    public function reflectOffset(
        TextDocument|TextDocument|string $sourceCode,
        ByteOffset|int $offset
    ): ReflectionOffset;

    public function reflectMethodCall(
        TextDocument|TextDocument|string $sourceCode,
        ByteOffset|int $offset
    ): ReflectionMethodCall;

    public function navigate(TextDocument|TextDocument|string $sourceCode): ReflectionNavigation;

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(TextDocument|TextDocument|string $sourceCode): Diagnostics;

    public function reflectNode(
        TextDocument|TextDocument|string $sourceCode,
        ByteOffset|int $offset
    ): ReflectionNode;

    public function reflectConstantsIn(
        TextDocument|TextDocument|string $sourceCode
    ): ReflectionDeclaredConstantCollection;

    /**
     * Walk the given source code's AST with the provided walker.
     * The walker is able to resolve nodes and has access to the frame.
     * @return Generator<int,null,null,?Frame>
     */
    public function walk(TextDocument $sourceCode, Walker $walker): Generator;
}
