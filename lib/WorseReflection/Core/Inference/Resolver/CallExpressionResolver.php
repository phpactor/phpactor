<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CallExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof CallExpression);
        $resolvableNode = $node->callableExpression;

        if ($resolvableNode instanceof Variable) {
            return $this->reosolveCallable($resolver, $frame, $resolvableNode);
        }

        return $resolver->resolveNode($frame, $resolvableNode);
    }

    private function reosolveCallable(NodeContextResolver $resolver, Frame $frame, Variable $variable): NodeContext
    {
        $type = $resolver->resolveNode($frame, $variable)->type();
        if (!$type instanceof CallableType) {
            return NodeContext::none();
        }

        return NodeContextFactory::create(
            NodeUtil::nameFromTokenOrNode($variable, $variable->name),
            $variable->getStartPosition(),
            $variable->getEndPosition(),
            [
                'type' => $type->returnType,
            ]
        );
    }
}
