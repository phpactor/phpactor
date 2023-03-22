<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class TernaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof TernaryExpression);

        $condition = $resolver->resolveNode($context, $node->condition);
        $left = NodeContext::none();
        $right = NodeContext::none();


        /** @phpstan-ignore-next-line */
        if ($node->ifExpression) {
            $context->frame()->applyTypeAssertions($condition->typeAssertions(), $node->ifExpression->getStartPosition());
            $left = $resolver->resolveNode($context, $node->ifExpression);
        }

        /** @phpstan-ignore-next-line */
        if (!$node->ifExpression) {
            $left = $condition;
        }

        /** @phpstan-ignore-next-line */
        if ($node->elseExpression) {
            $context->frame()->applyTypeAssertions($condition->typeAssertions()->negate(), $node->elseExpression->getStartPosition());
            $right = $resolver->resolveNode($context, $node->elseExpression);
        }

        $empty = $condition->type()->isEmpty();

        if ($empty->isFalse()) {
            return $context->withType($left->type());
        }

        if ($empty->isTrue()) {
            return $context->withType($right->type());
        }

        return $context->withType($left->type()->addType($right->type()));
    }
}
