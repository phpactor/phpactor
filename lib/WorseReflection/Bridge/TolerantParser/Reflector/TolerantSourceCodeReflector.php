<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflector;

use Generator;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\MethodCallNotFound;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection as TolerantReflectionClassCollection;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionOffset as TolerantReflectionOffset;
use Phpactor\WorseReflection\Core\Inference\NodeReflector;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection as TolerantReflectionFunctionCollection;

class TolerantSourceCodeReflector implements SourceCodeReflector
{
    public function __construct(
        private ServiceLocator $serviceLocator,
        private Parser $parser
    ) {
    }

    /**
     * @param array<string,bool> $visited
     */
    public function reflectClassesIn(
        TextDocument $sourceCode,
        array $visited = []
    ): ReflectionClassLikeCollection {
        $sourceCode = TextDocument::fromUnknown($sourceCode);
        $node = $this->parseSourceCode($sourceCode);
        return TolerantReflectionClassCollection::fromNode($this->serviceLocator, $sourceCode, $node, $visited);
    }

    public function reflectOffset(
        TextDocument $sourceCode,
        ByteOffset|int $offset
    ): ReflectionOffset {
        $sourceCode = TextDocument::fromUnknown($sourceCode);
        $offset = ByteOffset::fromUnknown($offset);

        $rootNode = $this->parseSourceCode($sourceCode);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $resolver = $this->serviceLocator->nodeContextResolver();
        $frame = $this->serviceLocator->frameBuilder()->build($node);

        return TolerantReflectionOffset::fromFrameAndSymbolContext($frame, $resolver->resolveNode($frame, $node));
    }

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(TextDocument $sourceCode): Diagnostics
    {
        $sourceCode = TextDocument::fromUnknown($sourceCode);
        return $this->serviceLocator->cache()->getOrSet(
            'diagnoistics__' . $sourceCode->__toString(),
            function () use ($sourceCode): Diagnostics {
                $rootNode = $this->parseSourceCode($sourceCode);
                $walker = $this->serviceLocator->newDiagnosticsWalker();
                $this->serviceLocator->frameBuilder()->withWalker($walker)->build($rootNode);
                return $walker->diagnostics();
            }
        );
    }

    public function walk(TextDocument $sourceCode, Walker $walker): Generator
    {
        $sourceCode = TextDocument::fromUnknown($sourceCode);
        $rootNode = $this->parseSourceCode($sourceCode);
        return $this->serviceLocator->frameBuilder()->withWalker($walker)->buildGenerator($rootNode);
    }

    public function reflectMethodCall(
        TextDocument $sourceCode,
        ByteOffset|int $offset
    ): ReflectionMethodCall {
        // see https://github.com/phpactor/phpactor/issues/1445
        $this->serviceLocator->cache()->purge();

        try {
            $reflection = $this->reflectNode($sourceCode, $offset);
        } catch (CouldNotResolveNode $notFound) {
            throw new MethodCallNotFound($notFound->getMessage(), 0, $notFound);
        }

        if (false === $reflection instanceof ReflectionMethodCall) {
            throw new MethodCallNotFound(sprintf(
                'Expected method call, got "%s"',
                get_class($reflection)
            ));
        }

        return $reflection;
    }

    public function reflectFunctionsIn(TextDocument $sourceCode): TolerantReflectionFunctionCollection
    {
        $sourceCode = TextDocument::fromUnknown($sourceCode);
        $node = $this->parseSourceCode($sourceCode);
        return TolerantReflectionFunctionCollection::fromNode($this->serviceLocator, $sourceCode, $node);
    }

    public function reflectConstantsIn(TextDocument $sourceCode): ReflectionDeclaredConstantCollection
    {
        $sourceCode = TextDocument::fromUnknown($sourceCode);
        $node = $this->parseSourceCode($sourceCode);
        return ReflectionDeclaredConstantCollection::fromNode($this->serviceLocator, $sourceCode, $node);
    }

    public function navigate(TextDocument $sourceCode): ReflectionNavigation
    {
        return new ReflectionNavigation($this->serviceLocator, $this->parseSourceCode(TextDocument::fromUnknown($sourceCode)));
    }

    public function reflectNode(
        TextDocument $sourceCode,
        ByteOffset|int $offset
    ): ReflectionNode {
        $sourceCode = TextDocumentBuilder::fromUnknown($sourceCode);
        $offset = ByteOffset::fromUnknown($offset);

        $rootNode = $this->parseSourceCode($sourceCode);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $frame = $this->serviceLocator->frameBuilder()->build($node);
        $nodeReflector = new NodeReflector($this->serviceLocator);

        return $nodeReflector->reflectNode($frame, $node);
    }

    private function parseSourceCode(TextDocument $sourceCode): SourceFileNode
    {
        $rootNode = $this->parser->parseSourceFile((string) $sourceCode, $sourceCode->path());
        return $rootNode;
    }
}
