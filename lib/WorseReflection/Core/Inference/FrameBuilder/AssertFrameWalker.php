<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;

class AssertFrameWalker extends AbstractInstanceOfWalker implements FrameWalker
{
    public function canWalk(Node $node): bool
    {
        if (false === $node instanceof CallExpression) {
            return false;
        }

        $name = $node->callableExpression->getText();

        return strtolower($name) == 'assert' && $node->argumentExpressionList !== null;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CallExpression);
        $list = $node->argumentExpressionList->getElements();
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $expressionIsTrue = $this->evaluator->evaluate($expression->expression);

            if (false === $expressionIsTrue) {
                continue;
            }

            $variables = $this->collectVariables($expression, $frame);

            foreach ($variables as $variable) {
                $this->getAssignmentsMatchingVariableType($frame, $variable)
                    ->add($variable)
                ;
            }
        }

        return $frame;
    }
}
