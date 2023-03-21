<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;

interface Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $parentContext, Node $node): NodeContext;
}
