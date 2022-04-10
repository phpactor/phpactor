<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node\Expression\ThrowExpression;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression;
use Phpactor\WorseReflection\Core\TypeFactory;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\BreakOrContinueStatement;
use Phpactor\WorseReflection\TypeUtil;

class IfStatementWalker extends AbstractInstanceOfWalker implements Walker
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
        $variables = $this->collectVariables($node, $frame);
        $variables = $this->mergeTypes($variables);
        $terminates = $this->branchTerminates($node);

        foreach ($variables as $variable) {
            $assignments = $this->getAssignmentsMatchingVariableType($frame, $variable);

            if ($terminates) {

                // reset variables after the if branch
                if ($expressionsAreTrue) {
                    $assignments->add($node->getStartPosition(), $variable);

                    // restore
                    $restoredVariable = $this->existingOrStripType($node, $frame, $variable);
                    $assignments->add($node->getEndPosition(), $restoredVariable);
                    continue;
                }

                // create new variables after the if branch
                if (false === $expressionsAreTrue) {
                    $detypedVariable = $variable->withType(TypeFactory::unknown());
                    $assignments->add($node->getStartPosition(), $detypedVariable);
                    $assignments->add($node->getEndPosition(), $variable);
                    continue;
                }
            }

            if ($expressionsAreTrue) {
                $assignments->add($node->getStartPosition(), $variable);
                $restoredVariable = $this->existingOrStripType($node, $frame, $variable);
                $assignments->add($node->getEndPosition(), $restoredVariable);
            }

            if (false === $expressionsAreTrue) {
                $variable = $variable->withType(TypeFactory::unknown());
                $assignments->add($node->getStartPosition(), $variable);
            }
        }

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
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param WorseVariable[] $variables
     * @return array<string,WorseVariable>
     */
    private function mergeTypes(array $variables): array
    {
        $vars = [];
        foreach ($variables as $variable) {
            if (isset($vars[$variable->name()])) {
                $originalVariable = $vars[$variable->name()];
                $variable = $originalVariable->withType(
                    TypeUtil::combine($originalVariable->type(), $variable->type())
                );
            }

            $vars[$variable->name()] = $variable;
        }

        /** @var array<string,WorseVariable> */
        return $vars;
    }

    private function existingOrStripType(IfStatementNode $node, Frame $frame, WorseVariable $variable): WorseVariable
    {
        $previousAssignments = $this->getAssignmentsMatchingVariableType($frame, $variable)
            ->lessThan($node->getStartPosition())
            ->byName($variable->name())
        ;

        if (0 === $previousAssignments->count()) {
            return $variable->withType(TypeFactory::unknown());
        }

        return $previousAssignments->last();
    }
}
