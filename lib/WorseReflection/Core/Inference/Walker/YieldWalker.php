<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\YieldExpression;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\GeneratorType;
use Phpactor\WorseReflection\Core\Type\MissingType;

class YieldWalker extends AbstractWalker
{
    public function nodeFqns(): array
    {
        return [YieldExpression::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof YieldExpression);

        $arrayElement = $node->arrayElement;
        $returnType = $frame->returnType();

        /** @phpstan-ignore-next-line No trust */
        if (!$arrayElement) {
            return $frame;
        }


        $key = new MissingType();
        if ($arrayElement->elementKey) {
            $key = $resolver->resolveNode($frame, $arrayElement->elementKey)->type();
        }
        $value = new MissingType();
        /** @phpstan-ignore-next-line No trust */
        if ($arrayElement->elementValue) {
            $value = $resolver->resolveNode($frame, $arrayElement->elementValue)->type();

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

            return $frame->withReturnType($returnType);
        }

        return $frame->withReturnType(
            TypeFactory::generator(
                $resolver->reflector(),
                $key,
                $value,
            )
        );
    }
}
