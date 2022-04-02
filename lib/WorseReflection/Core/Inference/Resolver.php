<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;

interface Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext;
}
