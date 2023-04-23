<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ConditionalType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\InvokeableType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CallExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof CallExpression);
        $resolvableNode = $node->callableExpression;

        $context = $resolver->resolveNode($frame, $resolvableNode);
        $returnType = $context->type();
        $containerType = $context->containerType();

        /** @todo: Check if this is even valid. Because it does seem to work perfectly! */
        if ($returnType instanceof MissingType && !($containerType instanceof MissingType)) {
            if ($containerType instanceof GenericClassType) {
                $reflected = $containerType->reflectionOrNull();
                $reflected->withGenericMap($containerType->arguments());

                try {
                    $reflectedReturn = $reflected->methods()->get($context->symbol()->name())->returnType();
                    return NodeContextFactory::forNode($node)->withType($reflectedReturn)->withContainerType($containerType);
                } catch (ItemNotFound) {
                }
            }
        }

        if ($returnType instanceof ConditionalType) {
            $context = $this->processConditionalType($returnType, $containerType, $context, $resolver, $frame, $node);
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
            return $context;
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
        Frame $frame,
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
                FunctionArguments::fromList($resolver, $frame, $node->argumentExpressionList)
            ));
        }

        if ($context->symbol()->symbolType() === Symbol::FUNCTION) {
            $function = $resolver->reflector()->reflectFunction($context->symbol()->name());
            return (new FunctionCallContext(
                $context->symbol(),
                ByteOffsetRange::fromInts(
                    $node->getStartPosition(),
                    $node->getEndPosition()
                ),
                $function
            ))->withType($type->evaluate(
                $function,
                FunctionArguments::fromList($resolver, $frame, $node->argumentExpressionList)
            ));
        }

        return $context;
    }
}
