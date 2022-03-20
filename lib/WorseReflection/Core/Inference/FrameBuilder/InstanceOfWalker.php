<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node\Expression\ThrowExpression;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression;
use Phpactor\WorseReflection\Core\Types;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\BreakOrContinueStatement;

class InstanceOfWalker extends AbstractInstanceOfWalker implements FrameWalker
{
    public function canWalk(Node $node): bool
    {
        return $node instanceof IfStatementNode;
    }

    /**
     * @param IfStatementNode $node
     */
    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        if (null === $node->expression) {
            return $frame;
        }

        assert($node instanceof IfStatementNode);

        if (false === $node->expression instanceof Expression) {
            return $frame;
        }

        $expressionsAreTrue = $this->evaluator->evaluate($node->expression);
        $variables = $this->collectVariables($node, $frame);
        $variables = $this->mergeTypes($variables);
        $terminates = $this->branchTerminates($node);

        foreach ($variables as $variable) {
            $assignments = $this->getAssignmentsMatchingVariableType($frame, $variable);

            if ($terminates) {

                // reset variables after the if branch
                if ($expressionsAreTrue) {
                    $assignments->add($variable);

                    // restore
                    $restoredVariable = $this->existingOrStripType($node, $frame, $variable);
                    $assignments->add($restoredVariable);
                    continue;
                }

                // create new variables after the if branch
                if (false === $expressionsAreTrue) {
                    $detypedVariable = $variable->withTypes(Types::empty());
                    $assignments->add($detypedVariable);
                    $variable = $variable->withOffset($node->getEndPosition());
                    $assignments->add($variable);
                    continue;
                }
            }

            if ($expressionsAreTrue) {
                $assignments->add($variable);
                $restoredVariable = $this->existingOrStripType($node, $frame, $variable);
                $assignments->add($restoredVariable);
            }

            if (false === $expressionsAreTrue) {
                $variable = $variable->withTypes(Types::empty());
                $assignments->add($variable);
            }
        }

        return $frame;
    }

    private function branchTerminates(IfStatementNode $node): bool
    {
        foreach ($node->statements as $list) {
            if (null === $list) {
                continue;
            }
            foreach ($list as $statement) {
                if ($statement instanceof ReturnStatement) {
                    return true;
                }

                if ($statement instanceof ThrowExpression) {
                    return true;
                }

                if ($statement instanceof CompoundStatementNode) {
                    foreach ($statement->statements as $statement) {
                        if ($statement instanceof BreakOrContinueStatement) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function mergeTypes(array $variables): array
    {
        $vars = [];
        foreach ($variables as $variable) {
            if (isset($vars[$variable->name()])) {
                $originalVariable = $vars[$variable->name()];
                $variable = $originalVariable->withTypes(
                    $originalVariable->symbolContext()
                        ->types()
                        ->merge(
                            $variable->symbolContext()->types()
                        )
                );
            }

            $vars[$variable->name()] = $variable;
        }

        return $vars;
    }

    private function existingOrStripType(IfStatementNode $node, Frame $frame, WorseVariable $variable)
    {
        $previousAssignments = $this->getAssignmentsMatchingVariableType($frame, $variable)
            ->lessThan($node->getStartPosition())
            ->byName($variable->name())
        ;

        if (0 === $previousAssignments->count()) {
            return $variable->withTypes(Types::empty())->withOffset($node->getEndPosition());
        }

        return $previousAssignments->last()->withOffset($node->getEndPosition());
    }
}
