<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Psr\Log\LoggerInterface;

class NodeContextResolver
{
    /**
     * @param array<class-name,Resolver> $resolverMap
     */
    public function __construct(
        private Reflector $reflector,
        private DocBlockFactory $docblockFactory,
        private LoggerInterface $logger,
        private Cache $cache,
        private array $resolverMap = []
    ) {
    }

    public function withCache(Cache $cache):self
    {
        return new self($this->reflector, $this->docblockFactory, $this->logger, $cache, $this->resolverMap);
    }

    /**
     * @param Node|Token|MissingToken $node
     */
    public function resolveNode(Frame $frame, $node): NodeContext
    {
        try {
            return $this->doResolveNodeWithCache($frame, $node);
        } catch (CouldNotResolveNode $couldNotResolveNode) {
            throw $couldNotResolveNode;
        }
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function docblockFactory(): DocBlockFactory
    {
        return $this->docblockFactory;
    }

    /**
     * Cache node look ups. Note that resolvers do not know about their parents
     * and will use the node resolver to fetch a parents context. This only
     * work if there is a cache. The cache should only have a lifetime of the
     * current operation.
     *
     * @param Node|Token|MissingToken|array<MissingToken> $node
     */
    private function doResolveNodeWithCache(Frame $frame, $node): NodeContext
    {
        // somehow we can get an array of missing tokens here instead of an object...
        if (!is_object($node)) {
            return NodeContext::none();
        }

        $key = 'sc:'.spl_object_id($node).':'.$frame->version();

        return $this->cache->getOrSet($key, function () use ($frame, $node) {
            if (false === $node instanceof Node) {
                throw new CouldNotResolveNode(sprintf(
                    'Non-node class passed to resolveNode, got "%s"',
                    get_class($node)
                ));
            }

            $context = $this->doResolveNode($frame, $node);
            $context = $context->withScope(new ReflectionScope($this->reflector, $node));

            return $context;
        });
    }

    private function doResolveNode(Frame $frame, Node $node): NodeContext
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));

        if (isset($this->resolverMap[get_class($node)])) {
            $resolver = $this->resolverMap[get_class($node)];
            return $resolver->resolve($this, $frame, $node);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));
    }
}
