<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node\ElseIfClauseNode;
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

        $frame = $this->ifBranch(
            $resolver,
            $frame,
            $node,
            $node->getStartPosition(),
            $this->resolveInitialEndPosition($node)
        );

        foreach ($node->elseIfClauses as $clause) {
            $frame = $this->ifBranch(
                $resolver,
                $frame,
                $clause,
                $clause->getStartPosition(),
                $clause->getEndPosition(),
            );
        }

        return $frame;
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function ifBranch(
        FrameResolver $resolver,
        Frame $frame,
        $node,
        int $start,
        int $end
    ): Frame {
        $context = $resolver->resolveNode($frame, $node->expression);
        $expressionsAreTrue = TypeUtil::toBool($context->type())->isTrue();
        $terminates = $this->branchTerminates($node);

        $frame->applyTypeAssertions($context->typeAssertions(), $start);

        if (!$terminates) {
            $frame->restoreToStateBefore($node->getStartPosition(), $end);
        }

        $context->typeAssertions()->negate();
        if ($terminates) {
            $frame->applyTypeAssertions($context->typeAssertions(), $start, $end);
        }

        if ($node instanceof IfStatementNode && $node->elseClause) {
            $frame->applyTypeAssertions($context->typeAssertions(), $start, $node->elseClause->getStartPosition());
            $frame->restoreToStateBefore($node->getStartPosition(), $node->elseClause->getEndPosition());
        }

        return $frame;
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function branchTerminates($node): bool
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

    private function resolveInitialEndPosition(IfStatementNode $node): int
    {
        foreach ($node->elseIfClauses as $clause) {
            return $clause->getStartPosition();
        }

        if ($node->elseClause) {
            return $node->elseClause->getStartPosition();
        }

        return $node->getEndPosition();
    }
}
