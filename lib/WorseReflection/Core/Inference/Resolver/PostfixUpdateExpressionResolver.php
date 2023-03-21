<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\PostfixUpdateExpression;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\NumericType;

class PostfixUpdateExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof PostfixUpdateExpression);
        $variable = $resolver->resolveNode($frame, $node->operand);
        $type = $variable->type();
        if ($type instanceof NumericType && $type instanceof Literal) {
            $value = $type->value();
            if (TokenKind::PlusPlusToken === $node->incrementOrDecrementOperator->kind) {
                return $variable->withType($type->withValue(++$value));
            }
            if (TokenKind::MinusMinusToken === $node->incrementOrDecrementOperator->kind) {
                return $variable->withType($type->withValue(--$value));
            }
        }
        return $variable;
    }
}
