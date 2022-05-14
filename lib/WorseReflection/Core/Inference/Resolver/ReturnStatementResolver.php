<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReturnStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        $context = NodeContextFactory::create('return', $node->getStartPosition(), $node->getEndPosition());
        assert($node instanceof ReturnStatement);

        if (!$node->expression) {
            return $context;
        }

        $type = $resolver->resolveNode($frame, $node->expression)->type();
        $context = $context->withType($type);

        if ($frame->returnType()->isDefined()) {
            $frame->withReturnType($frame->returnType()->addType($type));
            return $context;
        }

        $frame->withReturnType($type);

        return $context;
    }

}
