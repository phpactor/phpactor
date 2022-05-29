<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Psr\Log\LoggerInterface;

class NodeContextResolver
{
    private Reflector $reflector;
    
    private LoggerInterface $logger;
    
    /**
     * @var array<class-name,Resolver>
     */
    private array $resolverMap;
    
    /**
     * @param array<class-name,Resolver> $resolverMap
     */
    public function __construct(
        Reflector $reflector,
        LoggerInterface $logger,
        array $resolverMap = []
    ) {
        $this->logger = $logger;
        $this->reflector = $reflector;
        $this->resolverMap = $resolverMap;
    }

    /**
     * @param Node|Token|MissingToken $node
     */
    public function resolveNode(Frame $frame, $node): NodeContext
    {
        try {
            return $this->doResolveNodeWithCache($frame, $node);
        } catch (CouldNotResolveNode $couldNotResolveNode) {
            return NodeContextFactory::forNode($node)
                ->withIssue($couldNotResolveNode->getMessage());
        }
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    /**
     * @param Node|Token|MissingToken|array<MissingToken> $node
     */
    private function doResolveNodeWithCache(Frame $frame, $node): NodeContext
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

        $context = $this->doResolveNode($frame, $node);
        $context = $context->withScope(new ReflectionScope($this->reflector, $node));

        return $context;
    }

    private function doResolveNode(Frame $frame, Node $node): NodeContext
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));

        if (isset($this->resolverMap[get_class($node)])) {
            return $this->resolverMap[get_class($node)]->resolve($this, $frame, $node);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));
    }
}
