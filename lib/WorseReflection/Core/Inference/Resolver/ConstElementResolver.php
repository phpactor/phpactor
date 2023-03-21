<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ConstElement;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ConstElementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof ConstElement);
        return $context
            ->withSymbolName(
                (string)$node->getName(),
            )
            ->withSymbolType(Symbol::CONSTANT)
            ->withContainerType(NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node));
    }
}
