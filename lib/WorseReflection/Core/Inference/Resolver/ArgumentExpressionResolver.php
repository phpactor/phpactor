<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class ArgumentExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof ArgumentExpression);
        return $resolver->resolveNode($context, $node->expression);
    }
}
