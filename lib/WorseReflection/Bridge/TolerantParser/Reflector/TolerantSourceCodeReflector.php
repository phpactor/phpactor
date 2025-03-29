<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflector;

use Amp\Promise;
use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\MethodCallNotFound;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
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
use function Amp\call;
use function Amp\delay;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Microsoft\PhpParser\ClassLike;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

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
    ): TolerantReflectionClassCollection {
        $node = $this->parseSourceCode($sourceCode);
        return $this->reflectClassesFromNode($sourceCode, $node, $visited);
    }

    public function reflectOffset(
        TextDocument $sourceCode,
        ByteOffset|int $offset
    ): ReflectionOffset {
        $offset = ByteOffset::fromUnknown($offset);

        $rootNode = $this->parseSourceCode($sourceCode);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $resolver = $this->serviceLocator->nodeContextResolver();
        $frame = $this->serviceLocator->frameBuilder()->build($node);

        return TolerantReflectionOffset::fromFrameAndSymbolContext($frame, $resolver->resolveNode($frame, $node));
    }

    /**
     * @return Promise<Diagnostics<Diagnostic>>
     */
    public function diagnostics(TextDocument $sourceCode): Promise
    {
        return $this->serviceLocator->cacheForDocument()->getOrSet($sourceCode->uriOrThrow(), 'diagnostics', function () use ($sourceCode) {
            return call(function () use ($sourceCode) {
                $rootNode = $this->parseSourceCode($sourceCode);
                $walker = $this->serviceLocator->newDiagnosticsWalker();
                foreach ($this->serviceLocator->frameBuilder()->withWalker($walker)->buildGenerator($rootNode) as $tick) {
                    yield delay(0);
                }
                return $walker->diagnostics();
            });
        });
    }

    public function walk(TextDocument $sourceCode, Walker $walker): Generator
    {
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
        $node = $this->parseSourceCode($sourceCode);
        return TolerantReflectionFunctionCollection::fromNode($this->serviceLocator, $sourceCode, $node);
    }

    public function reflectConstantsIn(TextDocument $sourceCode): ReflectionDeclaredConstantCollection
    {
        $node = $this->parseSourceCode($sourceCode);
        return ReflectionDeclaredConstantCollection::fromNode($this->serviceLocator, $sourceCode, $node);
    }

    public function navigate(TextDocument $sourceCode): ReflectionNavigation
    {
        return new ReflectionNavigation($this->serviceLocator, $this->parseSourceCode($sourceCode));
    }

    public function reflectNodeContext(Node $node): NodeContext
    {
        $frame = $this->serviceLocator->frameBuilder()->build($node);
        return $this->serviceLocator->nodeContextResolver()->resolveNode($frame, $node);
    }


    public function reflectNode(
        TextDocument $sourceCode,
        ByteOffset|int $offset
    ): ReflectionNode {
        $offset = ByteOffset::fromUnknown($offset);

        $rootNode = $this->parseSourceCode($sourceCode);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $frame = $this->serviceLocator->frameBuilder()->build($node);
        $nodeReflector = new NodeReflector($this->serviceLocator);

        return $nodeReflector->reflectNode($frame, $node);
    }

    /**
     * @param array<string,bool> $visited
     */
    private function reflectClassesFromNode(TextDocument $source, Node $node, array $visited): TolerantReflectionClassCollection
    {
        $items = [];

        $nodeCollection = $node->getDescendantNodes(function (Node $node) {
            return false === $node instanceof ClassLike && false === $node instanceof ObjectCreationExpression;
        });

        foreach ($nodeCollection as $child) {
            if (false === $child instanceof ClassLike && !$child instanceof ObjectCreationExpression) {
                continue;
            }

            if ($child instanceof TraitDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionTrait($this->serviceLocator, $source, $child, $visited);
                continue;
            }

            if ($child instanceof EnumDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionEnum($this->serviceLocator, $source, $child);
                continue;
            }

            if ($child instanceof InterfaceDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionInterface($this->serviceLocator, $source, $child, $visited);
                continue;
            }

            if ($child instanceof ClassDeclaration) {
                $items[(string) $child->getNamespacedName()] = new ReflectionClass($this->serviceLocator, $source, $child, $visited);
                continue;
            }

            if ($child instanceof ObjectCreationExpression && !($child->classTypeDesignator instanceof Node)) {
                $items[NodeUtil::nameFromTokenOrNode($node, $child)] = new ReflectionClass($this->serviceLocator, $source, $child, $visited);
            }
        }

        return TolerantReflectionClassCollection::fromReflections($items);
    }

    private function parseSourceCode(TextDocument $sourceCode): SourceFileNode
    {
        return $this->parser->parseSourceFile((string) $sourceCode, $sourceCode->uri()?->__toString());
    }
}
