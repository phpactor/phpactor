<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression\YieldExpression;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Microsoft\PhpParser\Node\CatchClause;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\TypeFactory;

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
        if (!$arrayElement) {
            return $frame;
        }

        $value = $resolver->resolveNode($frame, $arrayElement->elementValue);
        return $frame->withReturnType(
            TypeFactory::generator(
                $resolver->reflector(),
                TypeFactory::arrayKey(),
                $value->type()->generalize()
            )
        );
    }
}
