<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class ObjectCreationExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ObjectCreationExpression);
        if (false === $node->classTypeDesignator instanceof Node) {
            throw new CouldNotResolveNode(sprintf('Could not create object from "%s"', get_class($node)));
        }

        return $resolver->resolveNode($frame, $node->classTypeDesignator);
    }
}
