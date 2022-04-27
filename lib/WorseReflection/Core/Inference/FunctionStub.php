<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;

interface FunctionStub
{
    public function resolve(NodeContext $context, FunctionArguments $args): NodeContext;
}
