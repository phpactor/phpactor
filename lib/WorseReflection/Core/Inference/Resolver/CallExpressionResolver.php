<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Context\CallContext;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ConditionalType;
use Phpactor\WorseReflection\Core\Type\InvokeableType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class CallExpressionResolver implements Resolver
{
    public function __construct(private GenericMapResolver $resolver)
    {
    }
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof CallExpression);
        $resolvableNode = $node->callableExpression;

        $context = $resolver->resolveNode($frame, $resolvableNode);
        $returnType = $context->type();
        $containerType = $context->containerType();

        if (
            $context instanceof CallContext && $context->arguments()
        ) {
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

    private function applyAssertions(
        CallContext $context,
        Frame $frame,
        CallExpression $node,
    ): void {
        $arguments = $context->arguments();
        $member = $context->callable();

        if (null === $arguments) {
            return;
        }

        $parameters = $member->parameters();
        if (count($member->docblock()->assertions()) === 0) {
            return;
        }
        $map = $this->resolver->mergeParameters($member->docblock()->templateMap(), $parameters, $arguments);
        foreach ($member->docblock()->assertions() as $assertion) {

            if (!$parameters->has($assertion->variableName)) {
                continue;
            }
            $param = $parameters->get($assertion->variableName);
            $arg = $arguments->at($param->index());
            $type = $assertion->type;
            if ($assertion->negated) {
                $type = TypeCombinator::subtract($assertion->type, $arg->type());
            }

            $frame->locals()->set(new PhpactorVariable(
                $arg->symbol()->name(),
                $node->getStartPosition(),
                $map->getOrGiven($type),
            ));
        }
    }
}
