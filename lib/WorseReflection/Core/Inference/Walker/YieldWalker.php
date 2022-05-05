<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\YieldExpression;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\TypeFactory;
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
        }

        return $frame->withReturnType(
            TypeFactory::generator(
                $resolver->reflector(),
                $key->generalize(),
                $value->generalize(),
            )
        );
    }
}
