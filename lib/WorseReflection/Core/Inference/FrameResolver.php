<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ArrowFunctionCreationExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Inference\Frame\ConcreteFrame;
use Phpactor\WorseReflection\Reflector;
use RuntimeException;

final class FrameResolver
{
    /**
     * @param Walker[] $globalWalkers
     * @param array<class-string,Walker[]> $nodeWalkers
     */
    public function __construct(
        private readonly NodeContextResolver $nodeContextResolver,
        private array $globalWalkers,
        private array $nodeWalkers,
        private readonly CacheForDocument $cache,
    ) {
    }

    /**
     * @param Walker[] $walkers
     */
    public static function create(
        NodeContextResolver $nodeContextResolver,
        array $walkers,
        CacheForDocument $cache,
    ): self {
        $globalWalkers = [];
        $nodeWalkers = [];
        foreach ($walkers as $walker) {
            if (empty($walker->nodeFqns())) {
                $globalWalkers[] = $walker;
                continue;
            }
            foreach ($walker->nodeFqns() as $key) {
                if (!isset($nodeWalkers[$key])) {
                    $nodeWalkers[$key] = [$walker];
                    continue;
                }
                $nodeWalkers[$key][] = $walker;
            }
        }

        return new self($nodeContextResolver, $globalWalkers, $nodeWalkers, $cache);
    }

    public function build(Node $node): Frame
    {
        $scopedNode = $this->resolveScopeNode($node);

        if (!$uri = $node->getUri()) {
            return $this->doBuild($scopedNode, $node);
        }

        $scopeKey = sprintf('scope:%s', spl_object_id($scopedNode));
        $scopeExtentKey = sprintf('scopex:%s', spl_object_id($scopedNode));

        $cache = $this->cache->cacheForDocument(TextDocumentUri::fromString($uri));

        if (null !== $scopeExtent = $cache->get($scopeExtentKey)) {
            if ($node->getStartPosition() > (int)$scopeExtent->scalar()) {
                $cache->remove($scopeKey);
            }
        }

        return $cache->getOrSet($scopeKey, function () use ($cache, $scopeExtentKey, $scopedNode, $node) {
            $cache->set($scopeExtentKey, $node->getStartPosition());
            return $this->doBuild($scopedNode, $node);
        });
    }

    /**
     * @return Generator<int,null,null,?Frame>
     */
    public function buildGenerator(Node $node): Generator
    {
        return $this->walkNode($this->resolveScopeNode($node), $node);
    }

    /**
     * @param Node|Token|MissingToken $node
     */
    public function resolveNode(Frame $frame, $node): NodeContext
    {
        $info = $this->nodeContextResolver->resolveNode($frame, $node);

        if ($info->issues()) {
            $frame->problems()->add($info);
        }

        return $info;
    }

    public function reflector(): Reflector
    {
        return $this->nodeContextResolver->reflector();
    }

    public function withWalker(Walker $walker): self
    {
        $new = $this;
        $new->globalWalkers[] = $walker;

        return $new;
    }

    public function withoutWalker(string $className): self
    {
        $new = $this;
        foreach ($this->globalWalkers as $walker) {
            if (get_class($walker) === $className) {
                continue;
            }
            $new->globalWalkers[] = $walker;
        }
        foreach ($this->nodeWalkers as $fqn => $walkers) {
            $new->nodeWalkers[$fqn] = array_filter($walkers, fn (Walker $walker) => get_class($walker) !== $className);
        }

        return $new;
    }

    public function resolver(): NodeContextResolver
    {
        return $this->nodeContextResolver;
    }

    private function doBuild(Node $scopedNode, Node $node): Frame
    {
        $generator = $this->walkNode($scopedNode, $node);
        foreach ($generator as $_) {
        }

        $frame = $generator->getReturn();
        if (!$frame) {
            throw new RuntimeException(
                'Walker did not return a Frame, this should never happen'
            );
        }

        return $frame;
    }

    /**
     * @return Generator<int,null,null,?Frame>
     */
    private function walkNode(Node $node, Node $targetNode, ?Frame $frame = null): Generator
    {
        if ($frame === null) {
            $frame = new ConcreteFrame();
        }

        foreach ($this->globalWalkers as $walker) {
            $frame = $walker->enter($this, $frame, $node);
        }

        $nodeClass = get_class($node);

        if (isset($this->nodeWalkers[$nodeClass])) {
            foreach ($this->nodeWalkers[$nodeClass] as $walker) {
                $frame = $walker->enter($this, $frame, $node);
            }
        }

        foreach ($node->getChildNodes() as $childNode) {
            $generator = $this->walkNode($childNode, $targetNode, $frame);
            yield from $generator;
            if ($found = $generator->getReturn()) {
                return $found;
            }
            yield;
        }

        if (isset($this->nodeWalkers[$nodeClass])) {
            foreach ($this->nodeWalkers[$nodeClass] as $walker) {
                $frame = $walker->exit($this, $frame, $node);
            }
        }

        foreach ($this->globalWalkers as $walker) {
            $frame = $walker->exit($this, $frame, $node);
        }

        // if we found what we were looking for then return it
        if ($node === $targetNode) {
            return $frame;
        }

        // we start with the source node and we finish with the source node.
        if ($node instanceof SourceFileNode) {
            return $frame;
        }

        return null;
    }

    private function resolveScopeNode(Node $node): Node
    {
        if ($node instanceof SourceFileNode) {
            return $node;
        }

        // do not traverse the whole source file for functions
        if ($node instanceof FunctionLike) {
            return $node;
        }

        $scopeNode = $node->getFirstAncestor(AnonymousFunctionCreationExpression::class, FunctionLike::class, SourceFileNode::class);

        if (null === $scopeNode) {
            throw new RuntimeException(sprintf(
                'Could not find scope node for "%s", this should not happen.',
                get_class($node)
            ));
        }

        // if this is an anonymous function, traverse the parent scope to
        // resolve any potential variable imports.
        if ($scopeNode instanceof AnonymousFunctionCreationExpression || $scopeNode instanceof ArrowFunctionCreationExpression) {
            return $this->resolveScopeNode($scopeNode->parent);
        }

        return $scopeNode;
    }
}
