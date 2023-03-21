<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class UseVariableNameResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof UseVariableName);
        $name = (string)$node->getName();

        return $context->replace(NodeContextFactory::forVariableAt(
            $context->frame(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            $name
        ));
    }
}
