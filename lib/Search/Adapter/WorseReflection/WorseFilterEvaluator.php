<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\Comparable;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use RuntimeException;

class WorseFilterEvaluator
{
    public function evaluate(Node $expression, TypedMatchTokens $vars): Type
    {
        if ($expression instanceof SourceFileNode) {
            return $this->evaluateSourceFileNode($expression, $vars);
        }
        if ($expression instanceof ExpressionStatement) {
            return $this->evaluate($expression->expression, $vars);
        }
        if ($expression instanceof BinaryExpression) {
            return TypeFactory::boolLiteral($this->evaluateBinaryStatement($expression, $vars)->isTrue());
        }
        if ($expression instanceof ReservedWord) {
            return $this->evaluateReservedWord($expression, $vars);
        }
        if ($expression instanceof Variable) {
            return $this->evaluateVariable($expression, $vars);
        }
        if ($expression instanceof StringLiteral) {
            return TypeFactory::stringLiteral((string)$expression->getStringContentsText());
        }
        if ($expression instanceof QualifiedName) {
            return TypeFactory::class((string)$expression->getResolvedName());
        }

        $this->cannotEvaluate($expression);
    }

    private function evaluateSourceFileNode(SourceFileNode $expression, TypedMatchTokens $vars): Type
    {
        foreach ($expression->statementList as $statement) {
            if ($statement instanceof InlineHtml) {
                continue;
            }
            return $this->evaluate($statement, $vars);
        }

        return TypeFactory::false();
    }

    private function evaluateBinaryStatement(BinaryExpression $expression, TypedMatchTokens $vars): BooleanType
    {
        $operator = $expression->operator;
        if ($operator->kind === TokenKind::InstanceOfKeyword) {
            return $this->evaluateInstanceOf($expression, $vars);
        }

        $leftType = $this->evaluate($expression->leftOperand, $vars);
        $rightType = $this->evaluate($expression->rightOperand, $vars);

        if ($leftType instanceof Comparable) {
            switch ($operator->kind) {
            case TokenKind::EqualsEqualsEqualsToken:
                return $leftType->identical($rightType);
            case TokenKind::EqualsEqualsToken:
                return $leftType->equal($rightType);
            case TokenKind::GreaterThanToken:
                return $leftType->greaterThan($rightType);
            case TokenKind::GreaterThanEqualsToken:
                return $leftType->greaterThanEqual($rightType);
            case TokenKind::LessThanToken:
                return $leftType->lessThan($rightType);
            case TokenKind::LessThanEqualsToken:
                return $leftType->lessThanEqual($rightType);
            case TokenKind::ExclamationEqualsToken:
                return $leftType->notEqual($rightType);
            case TokenKind::ExclamationEqualsEqualsToken:
                return $leftType->notIdentical($rightType);
            }
        }

        $this->cannotEvaluate($expression);
    }

    private function evaluateReservedWord(ReservedWord $expression, TypedMatchTokens $vars): BooleanType
    {
        if ($expression->getText() === 'true') {
            return TypeFactory::boolLiteral(true);
        }
        if ($expression->getText() === 'false') {
            return TypeFactory::boolLiteral(false);
        }

        $this->cannotEvaluate($expression);
    }

    /**
     * @return never
     */
    private function cannotEvaluate(Node $expression): void
    {
        throw new RuntimeException(sprintf(
            'Do not know how to evaluate "%s" (%s)', $expression->getText(), get_class($expression)
        ));
    }

    private function evaluateVariable(Variable $expression, TypedMatchTokens $vars): Type
    {
        $name = $expression->name;
        if (!$name instanceof Token) {
            return $this->cannotEvaluate($expression);
        }

        return TypeFactory::stringLiteral(
            $vars->get(ltrim(
                (string)$name->getText($expression->getFileContents()),
                '$'
            ))->token->text
        );
    }

    private function evaluateInstanceOf(BinaryExpression $expression, TypedMatchTokens $vars): Type
    {
        $leftOperand = $expression->leftOperand;
        $rightType = $this->evaluate($expression->rightOperand, $vars);
        if (!$leftOperand instanceof Variable) {
            throw new RuntimeException(
                'instanceof can only be used on variables'
            );
        }
        $name = ltrim(NodeUtil::nameFromTokenOrNode($expression, $leftOperand), '$');
        $match = $vars->get($name);

        return TypeFactory::boolLiteral($match->type->instanceof($rightType)->isTrue());
    }
}
