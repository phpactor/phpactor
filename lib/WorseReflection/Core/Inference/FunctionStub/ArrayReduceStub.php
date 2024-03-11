<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClosureType;

class ArrayReduceStub implements FunctionStub
{
    public function resolve(
        Frame $frame,
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        if (!$args->at(0)->type()->isDefined()) {
            return $context;
        }

        $closureType = $args->at(1);
        if (!$closureType instanceof ClosureType) {
            return $context;
        }

        return $context->withType(TypeFactory::array($closureType->returnType()));
    }
}
