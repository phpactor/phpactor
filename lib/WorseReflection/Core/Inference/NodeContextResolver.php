<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Inference\Resolver\VariableDocblockGlobalVisitor;
use Phpactor\WorseReflection\Core\NodeContextVisitors;
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
        private NodeContextVisitors $visitors,
        private array $resolverMap = [],
    ) {
    }

    public function withCache(Cache $cache):self
    {
        return new self($this->reflector, $this->docblockFactory, $this->logger, $cache, $this->resolverMap);
    }

    /**
     * @param Node|Token|MissingToken $node
     */
    public function resolveNode(NodeContext $parentContext, $node): NodeContext
    {
        try {
            return $this->doResolve($parentContext, $node);
        } catch (CouldNotResolveNode $couldNotResolveNode) {
            $this->logger->warning(sprintf('No resolver for: %s', get_class($node)));
            return NodeContext::none();
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
     * @param Node|Token|MissingToken|array<MissingToken> $node
     */
    private function doResolve(NodeContext $parentContext, $node): NodeContext
    {
        // somehow we can get an array of missing tokens here instead of an object...
        if (!is_object($node)) {
            return NodeContext::none();
        }

        if (false === $node instanceof Node) {
            throw new CouldNotResolveNode(sprintf(
                'Non-node class passed to resolveNode, got "%s"',
                get_class($node)
            ));
        }

        if (!isset($this->resolverMap[get_class($node)])) {
            throw new CouldNotResolveNode(sprintf(
                'Did not know how to resolve node of type "%s" with text "%s"',
                get_class($node),
                $node->getText()
            ));
        }

        $resolver = $this->resolverMap[get_class($node)];
        $this->logger->debug(sprintf('Resolving: %s with %s', get_class($node), $resolver::class));
        $context = NodeContextFactory::forNode($node)->withFrame($parentContext->frame());
        $context->parent = $parentContext;
        $context = (new VariableDocblockGlobalVisitor())->resolve($this, $context, $node);
        $context = $resolver->resolve(
            $this,
            $context,
            $node
        );
        foreach ($this->visitors->visitorsFor(get_class($node)) as $visitor) {
            $context = $visitor->visit($context);
        }
        return $context->withScope(new ReflectionScope($this->reflector, $node));
    }
}
