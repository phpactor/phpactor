<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Phpactor\WorseReflection\Core\Type;

interface FunctionStub
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, NodeContext $context, ArgumentExpressionList $node): NodeContext;
}
