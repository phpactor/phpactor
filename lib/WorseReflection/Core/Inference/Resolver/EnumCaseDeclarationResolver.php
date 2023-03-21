<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class EnumCaseDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof EnumCaseDeclaration);
        return $context
            ->withSymbolName(
                NodeUtil::nameFromTokenOrQualifiedName($node, $node->name),
            )
            ->withSymbolType(Symbol::CASE)
            ->withContainerType(NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node));
    }
}
