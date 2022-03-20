<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Core\Cache;
use RuntimeException;

final class FrameBuilder
{
    /**
     e @var SymbolContextResolver
     */
    private $symbolContextResolver;

    /**
     * @var FrameWalker[]
     */
    private array $walkers;
    
    private Cache $cache;

    /**
     * @param FrameWalker[] $walkers
     */
    public function __construct(SymbolContextResolver $symbolContextResolver, array $walkers, Cache $cache)
    {
        $this->symbolContextResolver = $symbolContextResolver;
        $this->walkers = $walkers;
        $this->cache = $cache;
    }

    /**
     * @param FrameWalker[] $walkers
     */
    public static function create(
        SymbolContextResolver $symbolContextResolver,
        Cache $cache,
        array $walkers = []
    ): self {
        return new self($symbolContextResolver, $walkers, $cache);
    }

    public function build(Node $node): Frame
    {
        return $this->walkNode($this->resolveScopeNode($node), $node);
    }

    /**
     * @internal For use with walkers
     *
     * TODO: Make an interface for this, extract it.
     */
    public function resolveNode(Frame $frame, $node): SymbolContext
    {
        $info = $this->symbolContextResolver->resolveNode($frame, $node);

        if ($info->issues()) {
            $frame->problems()->add($info);
        }

        return $info;
    }

    private function walkNode(Node $node, Node $targetNode, ?Frame $frame = null): ?Frame
    {
        $key = 'frame:'.spl_object_hash($targetNode);

        return $this->cache->getOrSet($key, function () use ($node, $targetNode, $frame) {
            if ($frame === null) {
                $frame = new Frame($node->getNodeKindName());
            }

            foreach ($this->walkers as $walker) {
                if ($walker->canWalk($node)) {
                    $frame = $walker->walk($this, $frame, $node);
                }
            }

            foreach ($node->getChildNodes() as $childNode) {
                if ($found = $this->walkNode($childNode, $targetNode, $frame)) {
                    return $found;
                }
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
        });
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

        // if this is an anonymous functoin, traverse the parent scope to
        // resolve any potential variable imports.
        if ($scopeNode instanceof AnonymousFunctionCreationExpression) {
            return $this->resolveScopeNode($scopeNode->parent);
        }

        return $scopeNode;
    }
}
