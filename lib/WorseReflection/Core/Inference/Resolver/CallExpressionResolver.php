<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ConditionalType;
use Phpactor\WorseReflection\Core\Type\InvokeableType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CallExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof CallExpression);
        $resolvableNode = $node->callableExpression;

        $resolvableContext = $resolver->resolveNode($context, $resolvableNode);
        $returnType = $resolvableContext->type();
        $containerType = $resolvableContext->containerType();

        if ($returnType instanceof ConditionalType) {
            $resolvableContext = $this->processConditionalType($returnType, $containerType, $resolvableContext, $resolver, $node);
        }

        if ($resolvableNode instanceof ParenthesizedExpression && $returnType instanceof ReflectedClassType && $returnType->isInvokable()) {
            return NodeContextFactory::forNode($node)
                ->withType($returnType->invokeType());
        }

        if ($returnType instanceof InvokeableType) {
            return NodeContextFactory::forNode($node)
                ->withType($returnType->returnType());
        }

        if (!$resolvableNode instanceof Variable) {
            return $resolvableContext;
        }

        if ($returnType instanceof ReflectedClassType && $returnType->isInvokable()) {
            return NodeContextFactory::forNode($node)
                ->withType($returnType->invokeType());
        }

        if (!$returnType instanceof InvokeableType) {
            return NodeContext::none();
        }

        return NodeContextFactory::create(
            NodeUtil::nameFromTokenOrNode($resolvableNode, $resolvableNode->name),
            $resolvableNode->getStartPosition(),
            $resolvableNode->getEndPosition(),
            [
                'type' => $returnType->returnType(),
            ]
        );
    }

    private function processConditionalType(
        ConditionalType $type,
        Type $containerType,
        NodeContext $context,
        NodeContextResolver $resolver,
        CallExpression $node
    ): NodeContext {
        if ($containerType instanceof ReflectedClassType) {
            $reflection = $containerType->reflectionOrNull();
            if (!$reflection) {
                return $context;
            }
            $method = $reflection->methods()->get($context->symbol()->name());
            return $context->withType($type->evaluate(
                $method,
                FunctionArguments::fromList($resolver, $context, $node->argumentExpressionList)
            ));
        }

        if ($context->symbol()->symbolType() === Symbol::FUNCTION) {
            $function = $resolver->reflector()->reflectFunction($context->symbol()->name());
            $args = FunctionArguments::fromList($resolver, $context, $node->argumentExpressionList);
            return (new FunctionCallContext(
                $context->symbol(),
                ByteOffsetRange::fromInts(
                    $node->getStartPosition(),
                    $node->getEndPosition()
                ),
                $function,
                $args
            ))->withType($type->evaluate(
                $function,
                $args
            ));
        }

        return $context;
    }
}
