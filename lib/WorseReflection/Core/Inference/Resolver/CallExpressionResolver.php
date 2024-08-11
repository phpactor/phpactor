<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ConditionalType;
use Phpactor\WorseReflection\Core\Type\InvokeableType;
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

        if ($context instanceof MemberAccessContext && $context->arguments()) {
            $this->applyAssertions($context, $frame, $node);
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
            $arguments = FunctionArguments::fromList($resolver, $frame, $node->argumentExpressionList);
            return (new FunctionCallContext(
                $context->symbol(),
                ByteOffsetRange::fromInts(
                    $node->getStartPosition(),
                    $node->getEndPosition()
                ),
                $function,
                $arguments,
            ))->withType($type->evaluate(
                $function,
                $arguments
            ));
        }

        return $context;
    }
    /**
     * @param MemberAccessContext<ReflectionMember> $context
     */
    private function applyAssertions(
        MemberAccessContext $context,
        Frame $frame,
        CallExpression $node,
    ): void {
        $member = $context->accessedMember();
        if (!$member instanceof ReflectionMethod) {
            return;
        }
        $arguments = $context->arguments();
        if (null === $arguments) {
            return;
        }

        $parameters = $member->parameters();
        foreach ($member->docblock()->assertions() as $assertion) {
            if (!$parameters->has($assertion->variableName)) {
                continue;
            }
            $param = $parameters->get($assertion->variableName);
            $arg = $arguments->at($param->index());
            $frame->locals()->set(new PhpactorVariable(
                $arg->symbol()->name(),
                $node->getStartPosition(),
                $assertion->type,
            ));
        }
    }
}
