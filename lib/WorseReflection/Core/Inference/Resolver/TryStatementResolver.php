<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\TryStatement;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;

class TryStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof TryStatement);
        $resolver->resolveNode($context, $node->compoundStatement);
        foreach ($node->catchClauses ?? [] as $clause) {
            $resolver->resolveNode($context, $clause);
        }
        return $context;
    }
}
