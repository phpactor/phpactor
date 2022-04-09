<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\UnaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type\BooleanType;
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
        $rightType = TypeUtil::toBool($operand->type());

        return $context->withType($rightType->negate());
    }
}
