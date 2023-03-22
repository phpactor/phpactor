<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\YieldExpression;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\GeneratorType;
use Phpactor\WorseReflection\Core\Type\MissingType;

class YieldExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof YieldExpression);
        $frame = $context->frame();

        $arrayElement = $node->arrayElement;
        /** @var Token */
        $from = $node->yieldOrYieldFromKeyword;
        $yieldFrom = $from->kind === TokenKind::YieldFromKeyword;
        $returnType = $frame->returnType();

        if (!$arrayElement) {
            return $context;
        }

        $key = new MissingType();
        if ($arrayElement->elementKey) {
            $key = $resolver->resolveNode($context, $arrayElement->elementKey)->type();
        }
        $value = new MissingType();
        /** @phpstan-ignore-next-line No trust */
        if ($arrayElement->elementValue) {
            $value = $resolver->resolveNode($context, $arrayElement->elementValue)->type();

            if ($yieldFrom) {
                $context->frame()->setReturnType($value);
                return $context;
            }

            // treat yield values as a seies of array shapes
            if ($value instanceof ArrayLiteral) {
                $value = $value->toShape();
            }
        }

        if ($returnType->isDefined() && $returnType instanceof GeneratorType) {
            if ($value->isDefined()) {
                $returnType = $returnType->withValue($returnType->valueType()->addType($value));
            }
            if ($key->isDefined()) {
                $returnType = $returnType->withKey($returnType->keyType()->addType($key));
            }

            $context->frame()->setReturnType($returnType);
            return $context;
        }

        $context->frame()->setReturnType(
            TypeFactory::generator(
                $resolver->reflector(),
                $key,
                $value,
            )
        );
        return $context;
    }
}
