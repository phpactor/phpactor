<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArrowFunctionCreationExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ArrowFunctionCreationExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ArrowFunctionCreationExpression);
        $type = NodeUtil::typeFromQualfiedNameLike(
            $resolver->reflector(),
            $node,
            $node->returnTypeList
        );

        if (!$type->isDefined()) {
            $type = $resolver->resolveNode($frame, $node->resultExpression)->type()->generalize();
        }

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => $type,
            ]
        );
    }
}
