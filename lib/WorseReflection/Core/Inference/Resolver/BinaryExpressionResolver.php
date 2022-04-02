<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\ExpressionEvaluator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;

class BinaryExpressionResolver implements Resolver
{
    private ExpressionEvaluator $expressionEvaluator;

    public function __construct()
    {
        $this->expressionEvaluator = new ExpressionEvaluator();
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof BinaryExpression);
        $value = $this->expressionEvaluator->evaluate($node);
        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::CLASS_,
                'type' => TypeFactory::fromValue($value),
                'value' => $value,
            ]
        );
    }
}
