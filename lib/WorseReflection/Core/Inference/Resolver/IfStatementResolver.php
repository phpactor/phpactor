<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\ElseIfClauseNode;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ExitIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\ThrowExpression;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\BreakOrContinueStatement;
use Phpactor\WorseReflection\Core\Type\NeverType;

class IfStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof IfStatementNode);

        if (null === $node->expression) {
            return $context;
        }


        if (!$node->expression instanceof Expression) {
            return $context;
        }

        // apply type assertions only within the if block
        $this->ifBranch(
            $resolver,
            $context,
            $node,
            $node->getStartPosition(),
            $this->resolveInitialEndPosition($node)
        );

        // apply type assertions only within the if block elseif clauses
        foreach ($node->elseIfClauses as $clause) {
            $this->ifBranch(
                $resolver,
                $context,
                $clause,
                $clause->getStartPosition(),
                $clause->getEndPosition(),
            );
        }

        // evaluate the nodes in the else clause
        if ($node->elseClause) {
            foreach ($node->elseClause->getChildNodes() as $child) {
                $resolver->resolveNode($context, $child);
            }
        }

        // restore state frame to what it was before the if statement
        foreach ($context->frame()->locals()->lessThan($node->getStartPosition())->mostRecent() as $assignment) {
            $context->frame()->locals()->set($assignment->withOffset($node->getEndPosition()));
        }

        // handle termination, negate the types if the branch terminates and
        // add any new assignments as a union
        $this->ifBranchTermination(
            $resolver,
            $context,
            $node,
            $node->getStartPosition(),
            $node->getEndPosition()
        );

        // handle terminateion for elseif clauses
        foreach ($node->elseIfClauses as $clause) {
            $this->ifBranchTermination(
                $resolver,
                $context,
                $clause,
                $node->getStartPosition(),
                $clause->getEndPosition(),
            );
        }

        // add any new assignments as unions to existing vars
        if ($node->elseClause) {
            $this->combineVariableAssignments($context, $node->elseClause, $node->getEndPosition());
        }

        return $context;
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function ifBranch(
        NodeContextResolver $resolver,
        NodeContext $context,
        $node,
        int $start,
        int $end
    ): void {
        $context = $resolver->resolveNode($context, $node->expression);
        $context->frame()->applyTypeAssertions($context->typeAssertions(), $start);

        foreach ($node->getChildNodes() as $child) {
            $resolver->resolveNode($context, $child);
        }

        $context->frame()->applyTypeAssertions(
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
        NodeContext $context,
        $node,
        int $start,
        int $end
    ): void {
        $context = $resolver->resolveNode($context, $node->expression);
        $terminates = $this->branchTerminates($resolver, $context, $node);

        if ($terminates) {
            $context->frame()->applyTypeAssertions(
                $context->typeAssertions()->negate(),
                $start,
                $end
            );
            return;
        }

        $this->combineVariableAssignments($context, $node, $end);
    }

    private function combineVariableAssignments(NodeContext $context, Node $node, int $end): void
    {
        foreach ($context->frame()->locals()->greaterThan($node->getStartPosition())->lessThan(
            $node->getEndPosition()
        )->mostRecent()->assignmentsOnly() as $assignment) {
            $context->frame()->locals()->add($assignment->withOffset($end), $node->getStartPosition());
        }
    }

    /**
     * @param IfStatementNode|ElseIfClauseNode $node
     */
    private function branchTerminates(NodeContextResolver $resolver, NodeContext $context, $node): bool
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
                        $context = $resolver->resolveNode($context, $callExpression);

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
