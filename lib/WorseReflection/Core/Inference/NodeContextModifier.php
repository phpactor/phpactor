<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\TypeUtil;

class NodeContextModifier
{
    public static function subtract(NodeContext $context): NodeContext
    {
        return $context
            ->withType(TypeUtil::toBool($context->type())->negate())
            ->withTypeAssertions($context->typeAssertions()->map(function (TypeAssertion $typeAssertion) {
                return $typeAssertion->withType(TypeFactory::not($typeAssertion->type()));
            }))
        ;
    }
}
