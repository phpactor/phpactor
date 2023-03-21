<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\ArrayAccessType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\TypeUtil;

class SubscriptExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof SubscriptExpression);
        $info = $resolver->resolveNode($frame, $node->postfixExpression);

        if (null === $node->accessExpression) {
            $info = $info->withIssue(sprintf(
                'Subscript expression "%s" is incomplete',
                (string) $node->getText()
            ));
            return $info;
        }

        $node = $node->accessExpression;
        $type = $info->type();

        if (!$type instanceof ArrayType) {
            $info = $info->withIssue(sprintf(
                'Not resolving subscript expression of type "%s"',
                (string) $info->type()
            ));
            return $info;
        }

        $arrayLiteralType = $info->type();
        $info = $info->withType($type->iterableValueType());

        if (!$arrayLiteralType instanceof ArrayAccessType) {
            $info = $info->withIssue(sprintf(
                'Array value for symbol "%s" is not an array, is a "%s"',
                (string) $info->symbol(),
                $arrayLiteralType->__toString()
            ));

            return $info;
        }

        if ($node instanceof StringLiteral) {
            $string = $resolver->resolveNode($frame, $node);

            $type = $arrayLiteralType->typeAtOffset(TypeUtil::valueOrNull($string->type()));
            if (($type->isDefined())) {
                return $string->withType($type);
            }
        }

        $info = $info->withIssue(sprintf(
            'Did not resolve access expression for node type "%s"',
            get_class($node)
        ));

        return $info;
    }
}
