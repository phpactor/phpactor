<?php

namespace Phpactor\WorseReflection\Core\Inference;

interface FunctionStub
{
    public function resolve(
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext;
}
