<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\IterableType;

class ArrayPopStub implements FunctionStub
{
    public function resolve(
        Frame $frame,
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
            $popped = array_pop($types);

            if (null === $popped) {
                return $context->withType(TypeFactory::null());
            }

            return $context->withType($popped);
        }

        $type = TypeFactory::mixed();
        if ($argType instanceof IterableType) {
            $type = $argType->iterableValueType();
        }

        return $context->withType(TypeFactory::union($type, TypeFactory::null()));
    }
}
