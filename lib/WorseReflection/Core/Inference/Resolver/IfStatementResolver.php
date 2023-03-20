<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\ElseIfClauseNode;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ExitIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\ThrowExpression;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\BreakOrContinueStatement;
use Phpactor\WorseReflection\Core\Type\NeverType;

class IfStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        $context = NodeContextFactory::forNode($node);
        assert($node instanceof IfStatementNode);

        if (null === $node->expression) {
            return $context;
        }


        if (!$node->expression instanceof Expression) {
            return $context;
        }
        $frame = $frameStack->current();

        // apply type assertions only within the if block
        $this->ifBranch(
            $resolver,
            $frameStack,
            $node,
            $node->getStartPosition(),
            $this->resolveInitialEndPosition($node)
        );

        // apply type assertions only within the if block elseif clauses
        foreach ($node->elseIfClauses as $clause) {
            $this->ifBranch(
                $resolver,
                $frameStack,
                $clause,
                $clause->getStartPosition(),
                $clause->getEndPosition(),
            );
        }

        // evaluate the nodes in the else clause
        if ($node->elseClause) {
            foreach ($node->elseClause->getChildNodes() as $child) {
                $resolver->resolveNode($frameStack, $child);
            }
        }

        // restore state frame to what it was before the if statement
        foreach ($frame->locals()->lessThan($node->getStartPosition())->mostRecent() as $assignment) {
            $frame->locals()->set($assignment->withOffset($node->getEndPosition()));
        }

        // handle termination, negate the types if the branch terminates and
        // add any new assignments as a union
        $this->ifBranchTermination(
            $resolver,
            $frameStack,
            $node,
            $node->getStartPosition(),
            $node->getEndPosition()
        );

        // handle terminateion for elseif clauses
        foreach ($node->elseIfClauses as $clause) {
            $this->ifBranchTermination(
                $resolver,
                $frameStack,
                $clause,
                $node->getStartPosition(),
                $clause->getEndPosition(),
            );
        }

        // add any new assignments as unions to existing vars
        if ($node->elseClause) {
            $this->combineVariableAssignments($frame, $node->elseClause, $node->getEndPosition());
        }

        return $context;
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function ifBranch(
        NodeContextResolver $resolver,
        FrameStack $frameStack,
        $node,
        int $start,
        int $end
    ): void {
        $context = $resolver->resolveNode($frameStack, $node->expression);
        $frameStack->current()->applyTypeAssertions($context->typeAssertions(), $start);

        foreach ($node->getChildNodes() as $child) {
            $resolver->resolveNode($frameStack, $child);
        }

        $frameStack->current()->applyTypeAssertions(
            $context->typeAssertions()->negate(),
            $start,
            $end
        );
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function ifBranchTermination(
        NodeContextResolver $resolver,
        FrameStack $frameStack,
        $node,
        int $start,
        int $end
    ): void {
        $context = $resolver->resolveNode($frameStack, $node->expression);
        $terminates = $this->branchTerminates($resolver, $frameStack, $node);

        if ($terminates) {
            $frameStack->current()->applyTypeAssertions(
                $context->typeAssertions()->negate(),
                $start,
                $end
            );
            return;
        }

        $this->combineVariableAssignments($frameStack->current(), $node, $end);
    }

    private function combineVariableAssignments(Frame $frame, Node $node, int $end): void
    {
        foreach ($frame->locals()->greaterThan($node->getStartPosition())->lessThan(
            $node->getEndPosition()
        )->mostRecent()->assignmentsOnly() as $assignment) {
            $frame->locals()->add($assignment->withOffset($end), $node->getStartPosition());
        }
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function branchTerminates(NodeContextResolver $resolver, FrameStack $frameStack, $node): bool
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

                    if ($callExpression = $statement->getFirstDescendantNode(CallExpression::class)) {
                        $context = $resolver->resolveNode($frameStack, $callExpression);

                        if ($context->type() instanceof NeverType) {
                            return true;
                        }
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
