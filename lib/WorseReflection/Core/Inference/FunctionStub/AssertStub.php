<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;

class AssertStub implements FunctionStub
{
    public function resolve(NodeContext $context, FunctionArguments $args): NodeContext
    {
        $context->frame()->applyTypeAssertions(
            $args->at(0)->typeAssertions(),
            $context->symbol()->position()->end()->toInt()
        );
        return $context;
    }
}
