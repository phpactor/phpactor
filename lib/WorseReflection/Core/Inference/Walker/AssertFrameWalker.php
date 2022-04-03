<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Phpactor\WorseReflection\Core\Inference\ExpressionVariableResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;

class AssertFrameWalker extends AbstractInstanceOfWalker implements Walker
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

            $variables = (new ExpressionVariableResolver())->resolve($resolver, $frame, $expression);

            foreach ($variables->combine() as $variable) {
                $this->getAssignmentsMatchingVariableType($frame, $variable)
                    ->add($node->getStartPosition(), $variable)
                ;
            }
        }

        return $frame;
    }
}
