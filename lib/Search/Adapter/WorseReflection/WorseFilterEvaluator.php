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
    public function evaluate(Node $expression, TypedMatchTokens $vars): TypedMatchTokens
    {
        if ($expression instanceof SourceFileNode) {
            return $this->evaluateSourceFileNode($expression, $vars);
        }
        if ($expression instanceof ExpressionStatement) {
            return $this->evaluate($expression->expression, $vars);
        }

        $this->cannotEvaluate($expression);
    }

    private function evaluateSourceFileNode(SourceFileNode $expression, TypedMatchTokens $vars): TypedMatchTokens
    {
        foreach ($expression->statementList as $statement) {
            if ($statement instanceof InlineHtml) {
                continue;
            }
            return $this->evaluate($statement, $vars);
        }

        return TypeFactory::false();
    }

    private function evaluateBinaryStatement(BinaryExpression $expression, TypedMatchTokens $vars): TypedMatchTokens
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

        throw new RuntimeException(sprintf(
            'Do not know how to evaluate `%s` (%s) with operator "%s" and left type: %s (%s)', $expression->getText(), get_class($expression), Token::getTokenKindNameFromValue($operator->kind), $leftType->__toString(), get_class($leftType)
        ));
    }

    private function evaluateReservedWord(ReservedWord $expression, TypedMatchTokens $vars): TypedMatchTokens
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

    private function evaluateVariable(Variable $expression, TypedMatchTokens $vars): TypedMatchTokens
    {
        $name = $expression->name;
        if (!$name instanceof Token) {
            return $this->cannotEvaluate($expression);
        }

        $union = [];
        $placeholderName = ltrim((string)$name->getText($expression->getFileContents()), '$');
        foreach ($vars->byName($placeholderName) as $token) {
            $union[] = TypeFactory::stringLiteral($token->token->text);
        }

        if (empty($union)) {
            throw new RuntimeException(sprintf(
                'Placeholder "%s" has not been defined', $placeholderName
            ));
        }

        return TypeFactory::union(...$union);
    }

    private function evaluateInstanceOf(BinaryExpression $expression, TypedMatchTokens $vars): TypedMatchTokens
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
