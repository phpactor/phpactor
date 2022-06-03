<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CallExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof CallExpression);
        $resolvableNode = $node->callableExpression;

        $context = $resolver->resolveNode($frame, $resolvableNode);
        $type = $context->type();

        if ($resolvableNode instanceof ParenthesizedExpression && $type instanceof ReflectedClassType && $type->isInvokable()) {
            return NodeContextFactory::forNode($node)
                ->withType($type->invokeType());
        }

        if (!$resolvableNode instanceof Variable) {
            return $context;
        }

        if ($type instanceof ReflectedClassType && $type->isInvokable()) {
            return NodeContextFactory::forNode($node)
                ->withType($type->invokeType());
        }

        if (!$type instanceof CallableType) {
            return NodeContext::none();
        }

        return NodeContextFactory::create(
            NodeUtil::nameFromTokenOrNode($resolvableNode, $resolvableNode->name),
            $resolvableNode->getStartPosition(),
            $resolvableNode->getEndPosition(),
            [
                'type' => $type->returnType,
            ]
        );
    }
}
