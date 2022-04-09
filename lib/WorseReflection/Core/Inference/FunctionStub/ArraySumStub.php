<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\NumericType;

class ArraySumStub implements FunctionStub
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, NodeContext $context, ArgumentExpressionList $node): NodeContext
    {
        $context = NodeContext::none();
        if (!$node->argumentExpressionList) {
            return $context;
        }

        foreach ($node->argumentExpressionList->getChildNodes() as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }
            $arg = $resolver->resolveNode($frame, $expression);
        }

        return $context;
    }
}
