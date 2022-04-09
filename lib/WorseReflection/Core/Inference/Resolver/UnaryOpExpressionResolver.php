<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\BitwiseOperable;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;

class UnaryOpExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof UnaryExpression);
        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
            ]
        );
        $operand = $resolver->resolveNode($frame, $node->operand);
        $operatorKind = NodeUtil::operatorKindForUnaryExpression($node);


        $type = $this->resolveType($operatorKind, $operand->type());
        return $context->withType($type);
    }

    private function resolveType(int $operatorKind, Type $type): Type
    {
        switch ($operatorKind) {
            case TokenKind::ExclamationToken:
                return TypeUtil::toBool($type)->negate();
            case TokenKind::PlusToken:
                return TypeUtil::toNumber($type)->identity();
            case TokenKind::MinusToken:
                return TypeUtil::toNumber($type)->negative();
            case TokenKind::TildeToken:
                if ($type instanceof BitwiseOperable) {
                    return $type->bitwiseNot();
                }
        }

        return new MissingType();
    }
}
