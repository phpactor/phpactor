<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;

class ReturnTypeWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [
            ReturnStatement::class,
        ];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if (!$node instanceof ReturnStatement) {
            return $frame;
        }

        if (!$node->expression) {
            return $frame;
        }

        $type = $resolver->resolveNode($frame, $node->expression)->type();

        if ($frame->returnType()->isDefined()) {
            return $frame->withReturnType($frame->returnType()->addType($type));
        }

        return $frame->withReturnType($type);
    }
}
