<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\IterableType;

class ResetStub implements FunctionStub
{
    public function resolve(
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $argType = $args->at(0)->type();
        if (!$argType->isArray()) {
            return $context;
        }

        $type = TypeFactory::mixed();
        if ($argType instanceof ArrayLiteral) {
            $type = $argType->typeAtOffset(0);

            if (!$type->isDefined()) {
                return $context->withType(TypeFactory::boolLiteral(false));
            }

            return $context->withType($type);
        }

        if ($argType instanceof IterableType) {
            $type = $argType->iterableValueType();
        }

        return $context->withType(TypeFactory::union($type, TypeFactory::boolLiteral(false)));
    }
}
