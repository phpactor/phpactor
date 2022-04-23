<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\IterableType;

class IteratorToArrayStub implements FunctionStub
{
    public function resolve(
        NodeContextResolver $resolver,
        Frame $frame,
        NodeContext $context,
        ArgumentExpressionList $node
    ): NodeContext
    {
        $context = $context->withType(TypeFactory::array());
        $args = [];
        foreach ($node->getChildNodes() as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
        }

        if (!isset($args[0])) {
            return $context;
        }

        $argType = $args[0]->type();

        if ($argType instanceof IterableType) {
            return $context->withType(TypeFactory::array($argType->iterableValueType()));
        }

        return $context;
    }
}
