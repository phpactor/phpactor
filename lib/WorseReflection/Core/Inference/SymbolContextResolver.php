<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CloneExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ClassLike;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Psr\Log\LoggerInterface;

class SymbolContextResolver
{
    private Reflector $reflector;
    
    private LoggerInterface $logger;
    
    private Cache $cache;

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
        Cache $cache,
        array $resolverMap = []
    ) {
        $this->logger = $logger;
        $this->reflector = $reflector;
        $this->cache = $cache;
        $this->resolverMap = $resolverMap;
    }

    /**
     * @param Node|Token $node
     */
    public function resolveNode(Frame $frame, $node): NodeContext
    {
        try {
            return $this->doResolveNodeWithCache($frame, $node);
        } catch (CouldNotResolveNode $couldNotResolveNode) {
            return NodeContext::none()
                ->withIssue($couldNotResolveNode->getMessage());
        }
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    /**
     * @param Node|Token $node
     */
    private function doResolveNodeWithCache(Frame $frame, $node): NodeContext
    {
        $key = 'sc:'.spl_object_hash($node);

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
            return $this->resolverMap[get_class($node)]->resolve($this, $frame, $node);
        }

        if ($node instanceof CloneExpression) {
            return $this->resolveCloneExpression($frame, $node);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));
    }



    /**
     * @return ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null
     */
    private function getClassLikeAncestor(Node $node)
    {
        $ancestor = $node->getFirstAncestor(ObjectCreationExpression::class, ClassLike::class);

        if ($ancestor instanceof ObjectCreationExpression) {
            if ($ancestor->classTypeDesignator instanceof Token) {
                if ($ancestor->classTypeDesignator->kind == TokenKind::ClassKeyword) {
                    throw new CouldNotResolveNode('Resolving anonymous classes is not currently supported');
                }
            }

            return $this->getClassLikeAncestor($ancestor);
        }

        /** @var ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null */
        return $ancestor;
    }

    private function resolveCloneExpression(Frame $frame, CloneExpression $node): NodeContext
    {
        return $this->doResolveNode($frame, $node->expression);
    }

    private function classTypeFromNode(Node $node): Type
    {
        return NodeUtil::nodeContainerClassLikeType($this->reflector, $node);
    }
}
