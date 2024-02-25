<?php

namespace Phpactor\WorseReflection\Core\Reflector\SourceCode;

use Amp\Promise;
use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;

class ContextualSourceCodeReflector implements SourceCodeReflector
{
    public function __construct(
        private SourceCodeReflector $innerReflector,
        private TemporarySourceLocator $locator
    ) {
    }

    public function reflectClassesIn(TextDocument $sourceCode, array $visited = []): ReflectionClassLikeCollection
    {
        $this->locator->pushSourceCode(TextDocumentBuilder::fromUnknown($sourceCode));

        $collection = $this->innerReflector->reflectClassesIn($sourceCode, $visited);

        return $collection;
    }

    public function reflectOffset(TextDocument $sourceCode, $offset): ReflectionOffset
    {
        $this->locator->pushSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectOffset($sourceCode, $offset);

        return $offset;
    }

    public function reflectMethodCall(TextDocument $sourceCode, $offset): ReflectionMethodCall
    {
        $this->locator->pushSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectMethodCall($sourceCode, $offset);

        return $offset;
    }

    public function reflectFunctionsIn(TextDocument $sourceCode): ReflectionFunctionCollection
    {
        $this->locator->pushSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectFunctionsIn($sourceCode);

        return $offset;
    }

    public function navigate(TextDocument $sourceCode): ReflectionNavigation
    {
        return $this->innerReflector->navigate($sourceCode);
    }

    public function diagnostics(TextDocument $sourceCode): Promise
    {
        $this->locator->pushSourceCode($sourceCode);
        return $this->innerReflector->diagnostics($sourceCode);
    }

    public function reflectNode(TextDocument $sourceCode, $offset): ReflectionNode
    {
        return $this->innerReflector->reflectNode($sourceCode, $offset);
    }

    public function reflectConstantsIn(TextDocument $sourceCode): ReflectionDeclaredConstantCollection
    {
        return $this->innerReflector->reflectConstantsIn($sourceCode);
    }

    public function walk(TextDocument $sourceCode, Walker $walker): Generator
    {
        return $this->innerReflector->walk($sourceCode, $walker);
    }

    public function reflectNodeContext(Node $node): NodeContext
    {
        return $this->innerReflector->reflectNodeContext($node);
    }
}
