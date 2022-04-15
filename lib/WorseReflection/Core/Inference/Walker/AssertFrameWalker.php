<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;

class AssertFrameWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [
            CallExpression::class,
        ];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if (!$node instanceof CallExpression) {
            return $frame;
        }

        $name = $node->callableExpression->getText();

        if (strtolower($name) !== 'assert' || $node->argumentExpressionList === null) {
            return $frame;
        }

        assert($node instanceof CallExpression);
        $list = $node->argumentExpressionList->getElements();
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $context = $resolver->resolveNode($frame, $expression->expression);

            $frame->applyTypeAssertions($context->typeAssertions(), $node->getEndPosition());
        }

        return $frame;
    }
}
