<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;

class ArgumentExpressionResolver implements Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        return $resolver->resolveNode($frame, $node->expression);

    }
}
