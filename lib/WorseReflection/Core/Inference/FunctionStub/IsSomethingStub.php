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
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;

class IsSomethingStub implements FunctionStub
{
    private Type $isType;

    public function __construct(Type $isType)
    {
        $this->isType = $isType;
    }

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
                $context = $context->withTypeAssertion(TypeAssertion::variable(
                    $variable,
                    fn (Type $type) => $this->isType,
                    fn (Type $type) => TypeCombinator::subtract($this->isType, $type),
                ));
            }

            $arg = $resolver->resolveNode($frame, $expression)->type();

            // extract to a variabe as it will not otherwise work with PHP 7.4
            $type = $this->isType;
            return $context->withType(new BooleanLiteralType($arg instanceof $type));
        }

        return $context;
    }
}
