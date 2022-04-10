<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\NullType;

class IsNullStub implements FunctionStub
{
    public function resolve(
        NodeContextResolver $resolver,
        Frame $frame,
        NodeContext $context,
        ArgumentExpressionList $node
    ): NodeContext {
        $context = NodeContext::none();

        foreach ($node->getChildNodes() as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $expression = $expression->expression;

            if ($expression instanceof Variable) {
                $variable = $expression->getName();
                $context = $context->withTypeAssertion(
                    TypeAssertion::variable($variable, TypeFactory::null())
                );
            }

            $arg = $resolver->resolveNode($frame, $expression)->type();

            return $context->withType(new BooleanLiteralType($arg instanceof NullType));
        }

        return $context;
    }
}
