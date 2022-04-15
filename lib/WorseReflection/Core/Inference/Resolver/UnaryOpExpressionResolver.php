<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\BitwiseOperable;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;

class UnaryOpExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof UnaryExpression);
        $operand = $resolver->resolveNode($frame, $node->operand);

        // see sister hack in BinaryExpressionResolver
        // https://github.com/Microsoft/tolerant-php-parser/issues/19
        $doubleNegate = $this->shouldDoubleNegate($node);

        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
            ]
        )->withTypeAssertions(
            $operand->typeAssertions()
        )->withType($operand->type());
        $operatorKind = NodeUtil::operatorKindForUnaryExpression($node);

        return $this->resolveType($context, $operatorKind, $operand->type(), $doubleNegate);
    }

    private function resolveType(NodeContext $context, int $operatorKind, Type $type, bool $doubleNegate): NodeContext
    {
        switch ($operatorKind) {
            case TokenKind::ExclamationToken:
                $context = $context->withType(
                    TypeUtil::toBool($context->type())->negate()
                );
                if ($doubleNegate) {
                    return $context;
                }
                return $context->negateTypeAssertions();
            case TokenKind::PlusToken:
                return $context->withType(TypeUtil::toNumber($type)->identity());
            case TokenKind::MinusToken:
                return $context->withType(TypeUtil::toNumber($type)->negative());
            case TokenKind::TildeToken:
                if ($type instanceof BitwiseOperable) {
                    return $context->withType($type->bitwiseNot());
                }
        }

        return $context;
    }

    private function shouldDoubleNegate(UnaryExpression $node): bool
    {
        /** @phpstan-ignore-next-line TPTodo */
        if (!$node->operand instanceof BinaryExpression) {
            return false;
        }

        /** @phpstan-ignore-next-line TPTodo */
        if (!$node->operand->leftOperand instanceof UnaryExpression) {
            return false;
        }

        $operatorKind = NodeUtil::operatorKindForUnaryExpression($node->operand->leftOperand);
        return $operatorKind === TokenKind::ExclamationToken;
    }
}
