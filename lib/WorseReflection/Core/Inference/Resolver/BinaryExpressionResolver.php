<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BitwiseOperable;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\Comparable;
use Phpactor\WorseReflection\Core\Type\Concatable;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;

class BinaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof BinaryExpression);

        $operator = $node->operator->kind;

        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
            ]
        );

        $left = $resolver->resolveNode($frame, $node->leftOperand);
        $right = $resolver->resolveNode($frame, $node->rightOperand);

        // merge type assertions from left AND right
        $context = $context->withTypeAssertions($left->typeAssertions()->merge($right->typeAssertions()));

        // resolve the type of the expression
        $context = $context->withType($this->walkBinaryExpression($left->type(), $right->type(), $operator));

        // work around for https://github.com/Microsoft/tolerant-php-parser/issues/19#issue-201714377
        // the left hand side of instanceof should be parsed as a variable but it's not.
        $leftOperand = $node->leftOperand;
        if ($leftOperand instanceof UnaryExpression) {
            $leftOperand = $leftOperand->operand;
            $left = $resolver->resolveNode($frame, $leftOperand);
        }

        if (!$leftOperand instanceof Node) {
            return $context->withIssue(sprintf('Left operand was not a node, got "%s"', get_class($leftOperand)));
        }
        if (!$node->rightOperand instanceof Node) {
            return $context->withIssue(sprintf('Right operand was not a node, got "%s"', get_class($node->rightOperand)));
        }

        // apply any type assertiosn (e.g. ===, instanceof, etc)
        $context = $this->applyTypeAssertions($context, $left, $right, $leftOperand, $node->rightOperand, $operator);

        // negate if there is a boolean comparison against an expression
        $context = $this->negate($context, $node->leftOperand, $node->rightOperand, $operator);

        return $context;
    }

    private function walkBinaryExpression(
        Type $left,
        Type $right,
        int $operator
    ): Type {
        if ($operator === TokenKind::QuestionQuestionToken) {
            return $this->nullCoalesce($left, $right);
        }
        if ($left instanceof Concatable) {
            switch ($operator) {
            case TokenKind::DotToken:
            case TokenKind::DotEqualsToken:
                return $left->concat($right);
            }
        }
        if ($left instanceof Comparable) {
            switch ($operator) {
            case TokenKind::EqualsEqualsEqualsToken:
                return $left->identical($right);
            case TokenKind::EqualsEqualsToken:
                return $left->equal($right);
            case TokenKind::GreaterThanToken:
                return $left->greaterThan($right);
            case TokenKind::GreaterThanEqualsToken:
                return $left->greaterThanEqual($right);
            case TokenKind::LessThanToken:
                return $left->lessThan($right);
            case TokenKind::LessThanEqualsToken:
                return $left->lessThanEqual($right);
            case TokenKind::ExclamationEqualsToken:
                return $left->notEqual($right);
            case TokenKind::ExclamationEqualsEqualsToken:
                return $left->notIdentical($right);
            }
        }

        switch ($operator) {
        case TokenKind::OrKeyword:
        case TokenKind::BarBarToken:
            return TypeUtil::toBool($left)->or(TypeUtil::toBool($right));
        case TokenKind::AndKeyword:
        case TokenKind::AmpersandAmpersandToken:
            return TypeUtil::toBool($left)->and(TypeUtil::toBool($right));
        case TokenKind::XorKeyword:
            return TypeUtil::toBool($left)->xor(TypeUtil::toBool($right));
        case TokenKind::PlusToken:
            return TypeUtil::toNumber($left)->plus(TypeUtil::toNumber($right));
        case TokenKind::MinusToken:
            return TypeUtil::toNumber($left)->minus(TypeUtil::toNumber($right));
        case TokenKind::AsteriskToken:
            return TypeUtil::toNumber($left)->multiply(TypeUtil::toNumber($right));
        case TokenKind::SlashToken:
            return TypeUtil::toNumber($left)->divide(TypeUtil::toNumber($right));
        case TokenKind::PercentToken:
            return TypeUtil::toNumber($left)->modulo(TypeUtil::toNumber($right));
        case TokenKind::AsteriskAsteriskToken:
            return TypeUtil::toNumber($left)->exp(TypeUtil::toNumber($right));
        }

        if ($left instanceof BitwiseOperable) {
            switch ($operator) {
            case TokenKind::AmpersandToken:
                return $left->bitwiseAnd($right);
            case TokenKind::BarToken:
                return $left->bitwiseOr($right);
            case TokenKind::CaretToken:
                return $left->bitwiseXor($right);
            case TokenKind::LessThanLessThanToken:
                return $left->shiftLeft($right);
            case TokenKind::GreaterThanGreaterThanToken:
                return $left->shiftRight($right);
            }
        }

        if ($left instanceof ClassType) {
            switch ($operator) {
            case TokenKind::InstanceOfKeyword:
                return TypeFactory::boolLiteral(true);
            }
        }

        return new MissingType();
    }

    private function applyTypeAssertions(
        NodeContext $context,
        NodeContext $leftContext,
        NodeContext $rightContext,
        Node $leftOperand,
        Node $rightOperand,
        int $operator
    ): NodeContext {
        switch ($operator) {
            case TokenKind::OrKeyword:
            case TokenKind::BarBarToken:
                return $context->withTypeAssertions(
                    $leftContext->typeAssertions()->or($rightContext->typeAssertions())
                );
            case TokenKind::AndKeyword:
            case TokenKind::AmpersandAmpersandToken:
                return $context->withTypeAssertions(
                    $leftContext->typeAssertions()->and($rightContext->typeAssertions())
                );

        }

        if (!NodeUtil::canAcceptTypeAssertion($leftOperand, $rightOperand)) {
            return $context;
        }

        [$reciever, $recieverContext ] = NodeUtil::canAcceptTypeAssertion(
            $leftOperand
        ) ? [$leftOperand, $leftContext] : [$rightOperand, $rightContext];
        [$transmitter, $transmittingContext ]  = NodeUtil::canAcceptTypeAssertion(
            $rightOperand
        ) ? [$leftOperand, $leftContext] : [$rightOperand, $rightContext];

        if (!NodeUtil::canAcceptTypeAssertion($reciever)) {
            return $context;
        }

        switch ($operator) {
            case TokenKind::EqualsEqualsEqualsToken:
                return $context->withTypeAssertion(TypeAssertion::forContext(
                    $recieverContext,
                    fn (Type $type) => $transmittingContext->type(),
                    fn (Type $type) => TypeCombinator::subtract($transmittingContext->type(), $recieverContext->type()),
                ));
            case TokenKind::InstanceOfKeyword:
                return $context->withTypeAssertion(TypeAssertion::forContext(
                    $recieverContext,
                    function (Type $type) use ($transmittingContext) {
                        $type = TypeCombinator::acceptedByType($type, TypeFactory::object());
                        $type = TypeCombinator::narrowTo($type, $transmittingContext->type());
                        return $type;
                    },
                    function (Type $type) use ($transmittingContext) {
                        $subtracted = TypeCombinator::subtract($transmittingContext->type(), $type);
                        return $subtracted;
                    }
                ));
        }

        return $context;
    }

    private function negate(
        NodeContext $context,
        Node $leftOperand,
        Node $rightOperand,
        int $operator
    ): NodeContext {
        $boolean = $leftOperand instanceof ReservedWord ? $leftOperand : $rightOperand;

        if (!$boolean instanceof ReservedWord) {
            return $context;
        }

        $text = $boolean->getText();

        // if this is an OR then we don't negate the type
        if (in_array($operator, [TokenKind::OrKeyword, TokenKind::BarBarToken])) {
            return $context;
        }

        if ($text === 'false') {
            $context->typeAssertions()->negate();
            return $context;
        }

        return $context;
    }

    private function nullCoalesce(Type $left, Type $right): Type
    {
        if ($left->isNullable()) {
            return TypeFactory::union($left->stripNullable(), $right);
        }

        if (!$left->isNull()) {
            return $left;
        }

        return $right;
    }
}
