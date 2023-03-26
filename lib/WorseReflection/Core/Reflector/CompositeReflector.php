<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Generator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;

class CompositeReflector implements Reflector
{
    public function __construct(
        private ClassReflector $classReflector,
        private SourceCodeReflector $sourceCodeReflector,
        private FunctionReflector $functionReflector,
        private ConstantReflector $constantReflector
    ) {
    }

    public function reflectClass($className): ReflectionClass
    {
        return $this->classReflector->reflectClass($className);
    }

    public function reflectInterface($className, array $visited = []): ReflectionInterface
    {
        return $this->classReflector->reflectInterface($className, $visited);
    }

    public function reflectTrait($className, array $visited = []): ReflectionTrait
    {
        return $this->classReflector->reflectTrait($className, $visited);
    }

    public function reflectEnum($className): ReflectionEnum
    {
        return $this->classReflector->reflectEnum($className);
    }

    public function reflectClassLike($className, $visited = []): ReflectionClassLike
    {
        return $this->classReflector->reflectClassLike($className, $visited);
    }


    public function reflectClassesIn(TextDocument|string $sourceCode, array $visited = []): ReflectionClassLikeCollection
    {
        return $this->sourceCodeReflector->reflectClassesIn($sourceCode, $visited);
    }


    public function reflectOffset(TextDocument $sourceCode, $offset): ReflectionOffset
    {
        return $this->sourceCodeReflector->reflectOffset($sourceCode, $offset);
    }

    public function reflectMethodCall(TextDocument $sourceCode, $offset): ReflectionMethodCall
    {
        return $this->sourceCodeReflector->reflectMethodCall($sourceCode, $offset);
    }

    public function reflectFunctionsIn(TextDocument $sourceCode): ReflectionFunctionCollection
    {
        return $this->sourceCodeReflector->reflectFunctionsIn($sourceCode);
    }

    public function navigate(TextDocument $sourceCode): ReflectionNavigation
    {
        return $this->sourceCodeReflector->navigate($sourceCode);
    }

    public function reflectFunction($name): ReflectionFunction
    {
        return $this->functionReflector->reflectFunction($name);
    }

    public function sourceCodeForClassLike($className): TextDocument
    {
        return $this->classReflector->sourceCodeForClassLike($className);
    }

    public function sourceCodeForFunction($name): TextDocument
    {
        return $this->functionReflector->sourceCodeForFunction($name);
    }

    public function diagnostics(TextDocument $sourceCode): Diagnostics
    {
        return $this->sourceCodeReflector->diagnostics($sourceCode);
    }

    public function reflectNode(TextDocument $sourceCode, $offset): ReflectionNode
    {
        return $this->sourceCodeReflector->reflectNode($sourceCode, $offset);
    }

    public function reflectConstantsIn(TextDocument $sourceCode): ReflectionDeclaredConstantCollection
    {
        return $this->sourceCodeReflector->reflectConstantsIn($sourceCode);
    }

    public function reflectConstant($name): ReflectionDeclaredConstant
    {
        return $this->constantReflector->reflectConstant($name);
    }

    public function sourceCodeForConstant($name): TextDocument
    {
        return $this->constantReflector->sourceCodeForConstant($name);
    }

    public function walk(TextDocument $sourceCode, Walker $walker): Generator
    {
        return $this->sourceCodeReflector->walk($sourceCode, $walker);
    }
}
