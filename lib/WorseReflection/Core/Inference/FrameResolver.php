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
use Phpactor\WorseReflection\Reflector;
use RuntimeException;

final class FrameResolver
{
    /**
     * @param Walker[] $globalWalkers
     * @param array<class-string,Walker[]> $nodeWalkers
     */
    public function __construct(
        private NodeContextResolver $nodeContextResolver,
        private array $globalWalkers,
        private array $nodeWalkers
    ) {
    }

    /**
     * @param Walker[] $walkers
     */
    public static function create(
        NodeContextResolver $nodeContextResolver,
        array $walkers = []
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

        return new self($nodeContextResolver, $globalWalkers, $nodeWalkers);
    }

    public function build(Node $node): FrameStack
    {
        $frameStack = FrameStack::new();
        $generator = $this->walkNode($this->resolveScopeNode($node), $node, $frameStack);
        foreach ($generator as $_) {
        }

        return $frameStack;
    }
    /**
     * @return Generator<int,null,null,FrameStack>
     */
    public function buildGenerator(Node $node): Generator
    {
        $frameStack = FrameStack::new();
        yield from $this->walkNode($this->resolveScopeNode($node), $node, $frameStack);
        return $frameStack;
    }

    /**
     * @param Node|Token|MissingToken $node
     */
    public function resolveNode(FrameStack $frameStack, $node): NodeContext
    {
        $info = $this->nodeContextResolver->resolveNode($frameStack, $node);

        if ($info->issues()) {
            $frameStack->current()->problems()->add($info);
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

    /**
     * @return Generator<int,null,null,bool>
     */
    private function walkNode(Node $node, Node $targetNode, FrameStack $frameStack): Generator
    {
        foreach ($this->globalWalkers as $walker) {
            $walker->enter($this, $frameStack, $node);
        }

        $nodeClass = get_class($node);

        if (isset($this->nodeWalkers[$nodeClass])) {
            foreach ($this->nodeWalkers[$nodeClass] as $walker) {
                $walker->enter($this, $frameStack, $node);
            }
        }

        foreach ($node->getChildNodes() as $childNode) {
            $generator = $this->walkNode($childNode, $targetNode, $frameStack);
            yield from $generator;
            if (true === $generator->getReturn()) {
                return true;
            }
            yield;
        }

        if (isset($this->nodeWalkers[$nodeClass])) {
            foreach ($this->nodeWalkers[$nodeClass] as $walker) {
                $walker->exit($this, $frameStack, $node);
            }
        }

        foreach ($this->globalWalkers as $walker) {
            $walker->exit($this, $frameStack, $node);
        }

        // if we found what we were looking for then return it
        if ($node === $targetNode) {
            return true;
        }

        // we start with the source node and we finish with the source node.
        if ($node instanceof SourceFileNode) {
            return true;
        }

        return false;
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
        if ($scopeNode instanceof AnonymousFunctionCreationExpression || $scopeNode instanceof ArrowFunctionCreationExpression) {
            return $this->resolveScopeNode($scopeNode->parent);
        }

        return $scopeNode;
    }
}
