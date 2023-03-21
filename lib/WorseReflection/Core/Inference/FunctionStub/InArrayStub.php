<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;

class InArrayStub implements FunctionStub
{
    public function resolve(
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $arg0 = $args->at(0);

        if ($arg0->symbol()->symbolType() !== Symbol::VARIABLE) {
            return $context->withType(TypeFactory::array());
        }

        $arrayType = $args->at(1)->type();
        if (!$arrayType instanceof ArrayLiteral) {
            return $context->withType(TypeFactory::array());
        }

        $union = TypeFactory::union(...$arrayType->iterableValueTypes());
        return $context->withTypeAssertion(
            TypeAssertion::forContext(
                $args->at(0),
                function (Type $type) use ($union) {
                    return $union;
                },
                function (Type $type) use ($union) {
                    $type = TypeCombinator::subtract($union, $type);
                    if (!$type->isDefined()) {
                        $type = $union->generalize();
                    }

                    return $type;
                }
            )
        );
    }
}
