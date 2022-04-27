<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
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
        $arrayType = $args->at(1)->type();
        if (!$arrayType instanceof ArrayLiteral) {
            return $context;
        }

        return $context->withTypeAssertion(
            TypeAssertion::forContext(
                $args->at(0),
                function (Type $type) use ($arrayType) {
                    return TypeFactory::union(...$arrayType->iterableValueTypes());
                },
                function (Type $type) {
                    return $type;
                }
            )
        );
    }
}
