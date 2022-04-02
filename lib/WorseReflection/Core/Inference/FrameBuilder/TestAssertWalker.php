<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use RuntimeException;

class TestAssertWalker implements FrameWalker
{
    public function nodeFqn(): ?string
    {
        return CallExpression::class;
    }

    public function canWalk(Node $node): bool
    {
        if (false === $node instanceof CallExpression) {
            return false;
        }

        $name = $node->callableExpression->getText();

        return $name == 'wrAssertType' && $node->argumentExpressionList !== null;
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CallExpression);
        $list = $node->argumentExpressionList->getElements();
        $args = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
        }

        $expectedType = $args[0]->value();
        $actualType = $args[1]->type();

        if ($actualType->__toString() !== $expectedType) {
            throw new RuntimeException(sprintf(
                'Type assertion failed: %s is not %s',
                $actualType->__toString(),
                $expectedType
            ));
        }

        return $frame;
    }
}
