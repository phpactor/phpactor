<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\ElseIfClauseNode;
use Microsoft\PhpParser\Node\Expression\ExitIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\ThrowExpression;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\BreakOrContinueStatement;
use Phpactor\WorseReflection\TypeUtil;

class IfStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        $context = NodeContext::none();
        assert($node instanceof IfStatementNode);

        if (null === $node->expression) {
            return $context;
        }


        if (!$node->expression instanceof Expression) {
            return $context;
        }

        $this->ifBranch(
            $resolver,
            $frame,
            $node,
            $node->getStartPosition(),
            $this->resolveInitialEndPosition($node)
        );

        foreach ($node->elseIfClauses as $clause) {
            $this->ifBranch(
                $resolver,
                $frame,
                $clause,
                $clause->getStartPosition(),
                $clause->getEndPosition(),
            );
        }

        return $context;
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function ifBranch(
        NodeContextResolver $resolver,
        Frame $frame,
        $node,
        int $start,
        int $end
    ): void {
        $context = $resolver->resolveNode($frame, $node->expression);
        $expressionsAreTrue = TypeUtil::toBool($context->type())->isTrue();
        $terminates = $this->branchTerminates($node);

        $frame->applyTypeAssertions($context->typeAssertions(), $start);

        foreach ($node->getChildNodes() as $child) {
            $resolver->resolveNode($frame, $child);
        }

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
