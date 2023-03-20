<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Frame;

class AssertStub implements FunctionStub
{
    public function resolve(Frame $frame, NodeContext $context, FunctionArguments $args): NodeContext
    {
        $frame->applyTypeAssertions(
            $args->at(0)->typeAssertions(),
            $context->symbol()->position()->endAsInt()
        );
        return $context;
    }
}
