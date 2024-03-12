<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;

class ArrayReduceStub implements FunctionStub
{
    public function resolve(
        Frame $frame,
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $initialType = $args->at(2)->type();
        if ($initialType->isDefined()) {
            return $context->withType($initialType->generalize());
        }

        return $context->withType(TypeFactory::array());

    }
}
