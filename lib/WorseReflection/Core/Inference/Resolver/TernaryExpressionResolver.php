<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Type\MissingType;

class TernaryExpressionResolver implements Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof TernaryExpression);

        // @phpstan-ignore-next-line
        if ($node->ifExpression) {
            $ifValue = $resolver->resolveNode($frame, $node->ifExpression);

            if (!$ifValue->type() instanceof MissingType) {
                return $ifValue;
            }
        }

        // if expression was not defined, fallback to condition
        $conditionValue = $resolver->resolveNode($frame, $node->condition);

        if (!$conditionValue->type() instanceof MissingType) {
            return $conditionValue;
        }

        return NodeContext::none();
    }
}
