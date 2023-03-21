<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\IterableType;

class ArrayShiftStub implements FunctionStub
{
    public function resolve(
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $arg = $args->at(0);
        $argType = $arg->type();
        if (!$argType->isArray()) {
            return $context;
        }

        if ($argType instanceof ArrayLiteral) {
            $types = $argType->types();
            $shifted = array_shift($types);

            if (null === $shifted) {
                return $context->withType(TypeFactory::null());
            }

            return $context->withType($shifted);
        }

        $type = TypeFactory::mixed();
        if ($argType instanceof IterableType) {
            $type = $argType->iterableValueType();
        }

        return $context->withType(TypeFactory::union($type, TypeFactory::null()));
    }
}
