<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\TypeUtil;
use RuntimeException;

class TestAssertWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [CallExpression::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CallExpression);
        $name = $node->callableExpression->getText();

        if ($name !== 'wrAssertType' || $node->argumentExpressionList === null) {
            return $frame;
        }

        $list = $node->argumentExpressionList->getElements();
        $args = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
        }

        // get string to compare against
        $expectedType = TypeUtil::valueOrNull($args[0]->type());
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
