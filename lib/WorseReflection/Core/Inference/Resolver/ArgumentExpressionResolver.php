<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class ArgumentExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $parentContext, Node $node): NodeContext
    {
        $context = $parentContext->addChildFromNode($node);
        assert($node instanceof ArgumentExpression);
        if (null === $node->expression) {
            return $context;
        }
        return $resolver->resolveNode($context, $node->expression);
    }
}
