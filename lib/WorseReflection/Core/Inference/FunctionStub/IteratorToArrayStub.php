<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\IterableType;

class IteratorToArrayStub implements FunctionStub
{
    public function resolve(
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $context = $context->withType(TypeFactory::array());
        if (!$args->at(0)->type()->isDefined()) {
            return $context;
        }

        $argType = $args->at(0)->type();

        if ($argType instanceof IterableType) {
            return $context->withType(TypeFactory::array($argType->iterableValueType()));
        }

        return $context;
    }
}
