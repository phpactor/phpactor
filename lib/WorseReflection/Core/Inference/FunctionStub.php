<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;

interface FunctionStub
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, NodeContext $context, ArgumentExpressionList $node): NodeContext;
}
