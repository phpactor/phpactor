<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class TernaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        $frame = $frameStack->current();
        assert($node instanceof TernaryExpression);

        $condition = $resolver->resolveNode($frameStack, $node->condition);
        $context = NodeContextFactory::create('trinary', $node->getStartPosition(), $node->getEndPosition());
        $left = NodeContext::none();
        $right = NodeContext::none();


        /** @phpstan-ignore-next-line */
        if ($node->ifExpression) {
            $frame->applyTypeAssertions($condition->typeAssertions(), $node->ifExpression->getStartPosition());
            $left = $resolver->resolveNode($frameStack, $node->ifExpression);
        }

        /** @phpstan-ignore-next-line */
        if (!$node->ifExpression) {
            $left = $condition;
        }

        /** @phpstan-ignore-next-line */
        if ($node->elseExpression) {
            $frame->applyTypeAssertions($condition->typeAssertions()->negate(), $node->elseExpression->getStartPosition());
            $right = $resolver->resolveNode($frameStack, $node->elseExpression);
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
