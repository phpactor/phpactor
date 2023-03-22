<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Microsoft\PhpParser\Node;

class ReturnStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof ReturnStatement);

        if (!$node->expression) {
            return $context;
        }

        $type = $resolver->resolveNode($context, $node->expression)->type();
        $context = $context->withType($type);
        $frame = $context->frame();

        if ($frame->returnType()->isVoid()) {
            $frame->setReturnType($type);
            return $context;
        }

        $frame->setReturnType($frame->returnType()->addType($type));

        return $context;
    }
}
