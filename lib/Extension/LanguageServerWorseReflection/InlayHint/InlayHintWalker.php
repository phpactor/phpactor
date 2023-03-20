<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\LanguageServerProtocol\InlayHintKind;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Context\ClassLikeContext;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

class InlayHintWalker implements Walker
{
    /**
     * @var InlayHint[]
     */
    private array $hints = [];

    public function __construct(private ByteOffsetRange $range, private InlayHintOptions $options)
    {
    }

    public function nodeFqns(): array
    {
        return [];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        if ($node->getStartPosition() < $this->range->start()->toInt()) {
            return;
        }
        if ($node->getEndPosition() > $this->range->end()->toInt()) {
            return;
        }
        if ($this->options->types && $node instanceof Variable) {
            $this->fromVariable($resolver, $frameStack, $node);
        }
        if ($this->options->params && $node instanceof CallExpression) {
            $this->fromCall($resolver, $frameStack, $node);
        }
        if ($this->options->params && $node instanceof ObjectCreationExpression) {
            $this->fromObjectCreation($resolver, $frameStack, $node);
        }
    }


    /**
     * @return InlayHint[]
     */
    public function hints(): array
    {
        return $this->hints;
    }

    private function fromCall(FrameResolver $resolver, FrameStack $frameStack, CallExpression $node): void
    {
        $parameters = (function (NodeContext $context): ?ReflectionParameterCollection {
            if ($context instanceof MemberAccessContext) {
                $method = $context->accessedMember();
                if (!$method instanceof ReflectionMethod) {
                    return null;
                }
                return $method->parameters();
            }

            if ($context instanceof FunctionCallContext) {
                return $context->function()->parameters();
            }

            return null;
        })($resolver->resolveNode($frameStack, $node));

        if (null === $parameters) {
            return;
        }

        foreach ($node->argumentExpressionList?->getValues() ?? [] as $index => $argument) {
            if (!$argument instanceof ArgumentExpression) {
                continue;
            }
            $parameter = $parameters->at($index);
            if (null === $parameter) {
                break;
            }
            $this->hints[] = new InlayHint(
                position: PositionConverter::intByteOffsetToPosition($argument->getStartPosition(), $node->getFileContents()),
                label: $parameter->name(),
                kind: InlayHintKind::PARAMETER,
                textEdits: null,
                tooltip: $parameter->type()->__toString(),
            );
        }
    }

    private function fromVariable(FrameResolver $resolver, FrameStack $frameStack, Variable $node): void
    {
        $name = $node->getName();
        $variable = $resolver->resolveNode($frameStack, $node);

        if (false === $variable->type()->isDefined()) {
            return;
        }

        $this->hints[] = new InlayHint(
            position: PositionConverter::intByteOffsetToPosition($node->getStartPosition(), $node->getFileContents()),
            label: $variable->type()->short(),
            tooltip: $variable->type()->__toString(),
            kind: InlayHintKind::TYPE,
            textEdits: null,
        );
    }

    private function fromObjectCreation(FrameResolver $resolver, FrameStack $frameStack, ObjectCreationExpression $node): void
    {
        $context = $resolver->resolveNode($frameStack, $node);
        if (!$context instanceof ClassLikeContext) {
            return;
        }
        $method = $context->classLike()->methods()->byName('__construct')->firstOrNull();
        if (!$method instanceof ReflectionMethod) {
            return;
        }

        $parameters = $method->parameters();
        foreach ($node->argumentExpressionList?->getValues() ?? [] as $index => $argument) {
            if (!$argument instanceof ArgumentExpression) {
                continue;
            }
            $parameter = $parameters->at($index);
            if (null === $parameter) {
                break;
            }
            $this->hints[] = new InlayHint(
                position: PositionConverter::intByteOffsetToPosition($argument->getStartPosition(), $node->getFileContents()),
                label: $parameter->name(),
                kind: InlayHintKind::PARAMETER,
                textEdits: null,
                tooltip: $parameter->type()->__toString(),
            );
        }
    }
}
