<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\ExpressionEvaluator;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Reflector;

abstract class AbstractInstanceOfWalker extends AbstractWalker
{
    protected ExpressionEvaluator $evaluator;

    private Reflector $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->evaluator = new ExpressionEvaluator();
        $this->reflector = $reflector;
    }

    /**
     * @return WorseVariable[]
     */
    protected function collectVariables(Node $node, Frame $frame): array
    {
        $variables = [];
        foreach ($node->getDescendantNodes() as $descendantNode) {
            if (!$descendantNode instanceof BinaryExpression) {
                continue;
            }

            $variable = $this->variableFromBinaryExpression($descendantNode, $frame);

            if (null === $variable) {
                continue;
            }

            $variables[] = $variable;
        }

        return $variables;
    }
    
    protected function variableFromBinaryExpression(BinaryExpression $node, Frame $frame): ?WorseVariable
    {
        $operator = $node->operator->getText($node->getFileContents());

        if (!is_string($operator)) {
            return null;
        }

        if (strtolower($operator) !== 'instanceof') {
            return null;
        }

        $variable = $node->getFirstDescendantNode(Variable::class);

        if (null === $variable) {
            return null;
        }

        // In case we are testing a property we don't want to change the type
        // of the underlying class but the type of the property
        if ($variable->getParent() instanceof MemberAccessExpression) {
            $variable = $variable->getParent();

            if ($variable->getParent() instanceof CallExpression) {
                return null; // Ignore if it's a method call
            }
        }

        $rightOperand = $node->rightOperand;

        if (false === $rightOperand instanceof QualifiedName) {
            return null;
        }

        $type = (string) $rightOperand->getResolvedName();

        $context = $this->createSymbolContext($variable, $frame);
        $context = $context->withType(TypeFactory::fromStringWithReflector($type, $this->reflector));
        $variable = WorseVariable::fromSymbolContext($context);

        return $variable;
    }

    protected function createSymbolContext(Expression $leftOperand, Frame $frame): SymbolContext
    {
        assert($leftOperand instanceof Variable || $leftOperand instanceof MemberAccessExpression);

        if ($leftOperand instanceof MemberAccessExpression) {
            return $this->createPropertySymbolContext($leftOperand, $frame);
        }

        return $this->createVariableSymbolContext($leftOperand);
    }

    protected function createPropertySymbolContext(
        MemberAccessExpression $leftOperand,
        Frame $frame
    ): SymbolContext {
        assert($leftOperand->dereferencableExpression instanceof Variable);

        $symbolContext = $this->symbolFactory()->context(
            (string) $leftOperand->memberName->getText($leftOperand->getFileContents()),
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            ['symbol_type' => Symbol::PROPERTY],
        );

        $classVariableName = $leftOperand->dereferencableExpression->getName();
        $assignments = $frame->locals()->byName($classVariableName);

        if (0 === $assignments->count()) {
            return $symbolContext
                ->withContainerType(TypeFactory::unknown())
                ->withIssue(sprintf('Variable "%s" is undefined', $classVariableName))
            ;
        }

        $classType = $assignments->first()->symbolContext()->types()->best();

        return $symbolContext->withContainerType($classType);
    }

    protected function createVariableSymbolContext(Variable $leftOperand): SymbolContext
    {
        $name = $leftOperand->getName();

        if (null === $name) {
            return SymbolContext::none();
        }

        return $this->symbolFactory()->context(
            $name,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            ['symbol_type' => Symbol::VARIABLE],
        );
    }

    /**
     * @return Assignments<WorseVariable>
     */
    protected function getAssignmentsMatchingVariableType(
        Frame $frame,
        WorseVariable $variable
    ): Assignments {
        if (Symbol::PROPERTY === $variable->symbolContext()->symbol()->symbolType()) {
            return $frame->properties();
        }

        return $frame->locals();
    }
}
