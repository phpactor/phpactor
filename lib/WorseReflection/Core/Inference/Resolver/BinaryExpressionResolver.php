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
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BitwiseOperable;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\Comparable;
use Phpactor\WorseReflection\Core\Type\Concatable;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;

class BinaryExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
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

        $left = $resolver->resolveNode($frameStack, $node->leftOperand);
        $right = $resolver->resolveNode($frameStack, $node->rightOperand);

        // merge type assertions from left AND right
        $context = $context->withTypeAssertions(
            $left->typeAssertions()->merge($right->typeAssertions())
        );

        // resolve the type of the expression
        $context = $context->withType(
            $this->walkBinaryExpression(
                $left->type(),
                $right->type(),
                $operator
            )
        );

        // work around for https://github.com/Microsoft/tolerant-php-parser/issues/19#issue-201714377
        // the left hand side of instanceof should be parsed as a variable but it's not.
        $leftOperand = $node->leftOperand;
        if ($leftOperand instanceof UnaryExpression) {
            $leftOperand = $leftOperand->operand;
            $left = $resolver->resolveNode($frameStack, $leftOperand);
        }

        if (!$leftOperand instanceof Node) {
            return $context->withIssue(sprintf('Left operand was not a node, got "%s"', get_class($leftOperand)));
        }

        if (!$node->rightOperand instanceof Node) {
            return $context->withIssue(sprintf('Right operand was not a node, got "%s"', get_class($node->rightOperand)));
        }

        // apply any type assertions (e.g. ===, instanceof, etc)
        $context = $this->applyTypeAssertions(
            $context,
            $left,
            $right,
            $leftOperand,
            $node->rightOperand,
            $operator
        );

        if (!$node->leftOperand instanceof Node) {
            return $context->withIssue(sprintf('Left operand was not a node, got "%s"', get_class($leftOperand)));
        }

        // negate if there is a boolean comparison against an expression
        $context = $this->negate(
            $context,
            $node->leftOperand,
            $node->rightOperand,
            $operator
        );

        $this->addVariable($operator, $frame, $leftOperand, $context);

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
            $value = null;
            $value = match ($operator) {
                TokenKind::EqualsEqualsEqualsToken => $left->identical($right),
                TokenKind::EqualsEqualsToken => $left->equal($right),
                TokenKind::GreaterThanToken => $left->greaterThan($right),
                TokenKind::GreaterThanEqualsToken => $left->greaterThanEqual($right),
                TokenKind::LessThanToken => $left->lessThan($right),
                TokenKind::LessThanEqualsToken => $left->lessThanEqual($right),
                TokenKind::ExclamationEqualsToken => $left->notEqual($right),
                TokenKind::ExclamationEqualsEqualsToken => $left->notIdentical($right),
                default => null,
            };
            if ($value !== null) {
                return $value;
            }
        }

        if ($left instanceof ArrayType) {
            switch ($operator) {
                case TokenKind::PlusToken:
                    return $left->mergeType($right);
            }
        }

        $value = match ($operator) {
            TokenKind::OrKeyword, TokenKind::BarBarToken => TypeUtil::toBool($left)->or(TypeUtil::toBool($right)),
            TokenKind::AndKeyword, TokenKind::AmpersandAmpersandToken => TypeUtil::toBool($left)->and(TypeUtil::toBool($right)),
            TokenKind::XorKeyword => TypeUtil::toBool($left)->xor(TypeUtil::toBool($right)),
            TokenKind::PlusToken => TypeUtil::toNumber($left)->plus(TypeUtil::toNumber($right)),
            TokenKind::MinusToken => TypeUtil::toNumber($left)->minus(TypeUtil::toNumber($right)),
            TokenKind::AsteriskToken => TypeUtil::toNumber($left)->multiply(TypeUtil::toNumber($right)),
            TokenKind::SlashToken => TypeUtil::toNumber($left)->divide(TypeUtil::toNumber($right)),
            TokenKind::PercentToken => TypeUtil::toNumber($left)->modulo(TypeUtil::toNumber($right)),
            TokenKind::AsteriskAsteriskToken => TypeUtil::toNumber($left)->exp(TypeUtil::toNumber($right)),
            default => null,
        };
        if ($value !== null) {
            return $value;
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
        return match ($operator) {
            TokenKind::EqualsEqualsEqualsToken => $context->withTypeAssertion(TypeAssertion::forContext(
                $recieverContext,
                fn (Type $type) => $transmittingContext->type(),

                // ???
                fn (Type $type) => TypeCombinator::subtract($transmittingContext->type(), $type),
            )),
            TokenKind::InstanceOfKeyword => $context->withTypeAssertion(TypeAssertion::forContext(
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
            )),
            default => $context,
        };
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

    private function addVariable(
        int $operator,
        Frame $frame,
        Node $leftOperand,
        NodeContext $context
    ): void {
        if (!$leftOperand instanceof Variable) {
            return;
        }

        if (!in_array($operator, [
            TokenKind::DotEqualsToken,
            TokenKind::DotToken,
        ])) {
            return;
        }

        $name = NodeUtil::nameFromTokenOrNode($leftOperand, $leftOperand->name);
        $context = NodeContextFactory::create(
            $name,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $context->type(),
            ]
        );

        $frame->locals()->set(PhpactorVariable::fromSymbolContext($context));
    }
}
