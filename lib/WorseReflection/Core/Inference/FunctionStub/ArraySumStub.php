<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;

class ArraySumStub implements FunctionStub
{
    public function resolve(
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $arg0 = $args->at(0)->type();
        if ($arg0 instanceof ArrayLiteral) {
            $context = $context->withType(
                TypeFactory::fromValue(array_sum($arg0->value()))
            );
            return $context;
        }

        return $context;
    }
}
