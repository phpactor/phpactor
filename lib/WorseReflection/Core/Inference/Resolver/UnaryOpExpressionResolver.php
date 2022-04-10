<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextModifier;
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
        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
            ]
        )->withTypeAssertions($operand->typeAssertions())->withType($operand->type());
        $operatorKind = NodeUtil::operatorKindForUnaryExpression($node);

        return $this->resolveType($context, $operatorKind, $operand->type());
    }

    private function resolveType(NodeContext $context, int $operatorKind, Type $type): NodeContext
    {
        switch ($operatorKind) {
        case TokenKind::ExclamationToken:
                return NodeContextModifier::negate($context);
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
}
