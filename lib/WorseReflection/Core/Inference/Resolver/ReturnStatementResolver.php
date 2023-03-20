<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;

class ReturnStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        $frame = $frameStack->current();
        $context = NodeContextFactory::forNode($node);
        assert($node instanceof ReturnStatement);

        if (!$node->expression) {
            return $context;
        }

        $type = $resolver->resolveNode($frameStack, $node->expression)->type();
        $context = $context->withType($type);

        if ($frame->returnType()->isVoid()) {
            $frame->setReturnType($type);
            return $context;
        }

        $frame->setReturnType($frame->returnType()->addType($type));

        return $context;
    }
}
