<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;

class AssertFrameWalker extends AbstractInstanceOfWalker implements FrameWalker
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
