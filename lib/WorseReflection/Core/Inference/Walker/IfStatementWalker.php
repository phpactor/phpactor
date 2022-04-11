<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node\Expression\ExitIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\ThrowExpression;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\BreakOrContinueStatement;
use Phpactor\WorseReflection\TypeUtil;

class IfStatementWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [IfStatementNode::class];
    }

    /**
     * @param IfStatementNode $node
     */
    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if (null === $node->expression) {
            return $frame;
        }

        assert($node instanceof IfStatementNode);

        if (!$node->expression instanceof Expression) {
            return $frame;
        }

        $context = $resolver->resolveNode($frame, $node->expression);
        $expressionsAreTrue = TypeUtil::toBool($context->type())->isTrue();
        $terminates = $this->branchTerminates($node);
        $originalFrame = clone $frame;

        $frame->applyTypeAssertions($context->typeAssertions(), $node->expression->getStartPosition());
        $frame->restoreToStateBefore($node->getStartPosition(), $node->getEndPosition());

        if (!$terminates) {
            return $frame;
        }
        $frame->applyTypeAssertions($context->typeAssertions()->negate(), $node->getEndPosition());

        return $frame;
    }

    private function branchTerminates(IfStatementNode $node): bool
    {
        /** @phpstan-ignore-next-line lies */
        foreach ($node->statements as $list) {
            if (null === $list) {
                continue;
            }
            foreach ($list as $statement) {
                if (!is_object($statement)) {
                    continue;
                }
                if ($statement instanceof ReturnStatement) {
                    return true;
                }

                if ($statement instanceof ExpressionStatement) {
                    if ($statement->expression instanceof ThrowExpression) {
                        return true;
                    }
                }

                if ($statement instanceof ThrowExpression) {
                    return true;
                }

                if ($statement instanceof CompoundStatementNode) {
                    foreach ($statement->statements as $statement) {
                        if ($statement instanceof BreakOrContinueStatement) {
                            return true;
                        }

                        if ($statement instanceof ExpressionStatement) {
                            if ($statement->expression instanceof ExitIntrinsicExpression) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
