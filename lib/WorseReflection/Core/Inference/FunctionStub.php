<?php

namespace Phpactor\WorseReflection\Core\Inference;

interface FunctionStub
{
    public function resolve(
        Frame $frame,
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext;
}
