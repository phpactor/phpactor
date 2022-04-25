<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;

class TernaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof TernaryExpression);

        $condition = $resolver->resolveNode($frame, $node->condition);
        $left = NodeContext::none();
        $right = NodeContext::none();


        if ($node->ifExpression) {
            $frame->applyTypeAssertions($condition->typeAssertions(), $node->ifExpression->getStartPosition());
            $left = $resolver->resolveNode($frame, $node->ifExpression);
        } else {
            $left = $condition;
        }

        if ($node->elseExpression) {
            $frame->applyTypeAssertions($condition->typeAssertions()->negate(), $node->elseExpression->getStartPosition());
            $right = $resolver->resolveNode($frame, $node->elseExpression);
        }

        $empty = $condition->type()->isEmpty();

        if ($empty->isFalse()) {
            return $left;
        }

        if ($empty->isTrue()) {
            return $right;
        }

        return $left->withType($left->type()->addToUnion($right->type()));
    }
}
