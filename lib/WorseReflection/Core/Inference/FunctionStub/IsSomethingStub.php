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
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;

class IsSomethingStub implements FunctionStub
{
    public function __construct(private Type $isType)
    {
    }

    public function resolve(
        Frame $frame,
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $arg0 = $args->at(0);

        $symbol = $arg0->symbol();
        if ($symbol->symbolType() === Symbol::VARIABLE) {
            $context = $context->withTypeAssertion(TypeAssertion::variable(
                $symbol->name(),
                $symbol->position()->startAsInt(),
                fn (Type $type) => TypeCombinator::narrowTo($type, $this->isType),
                function (Type $type) {
                    return TypeCombinator::subtract($this->isType, $type);
                }
            ));
        }

        $argType = $arg0->type();

        // extract to a variabe as it will not otherwise work with PHP 7.4
        $type = $this->isType;
        return $context->withType(new BooleanLiteralType($argType instanceof $type));
    }
}
