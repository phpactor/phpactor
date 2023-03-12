<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\LanguageServerProtocol\InlayHintKind;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

class InlayHintWalker implements Walker
{
    /**
     * @var InlayHint[]
     */
    private array $hints = [];

    public function __construct(private ByteOffsetRange $range)
    {
    }

    public function nodeFqns(): array
    {
        return [];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if ($node->getStartPosition() < $this->range->start()->toInt()) {
            return $frame;
        }
        if ($node->getEndPosition() > $this->range->end()->toInt()) {
            return $frame;
        }
        if ($node instanceof Variable) {
            $this->fromVariable($resolver, $frame, $node);
            return $frame;
        }
        if ($node instanceof CallExpression) {
            $this->fromCall($resolver, $frame, $node);
            return $frame;
        }
        return $frame;
    }


    /**
     * @return InlayHint[]
     */
    public function hints(): array
    {
        return $this->hints;
    }

    private function fromCall(FrameResolver $resolver, Frame $frame, CallExpression $node): void
    {
        $context = $resolver->resolveNode($frame, $node);
        if (!$context instanceof MemberAccessContext) {
            return;
        }
        $method = $context->accessedMember();
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

    private function fromVariable(FrameResolver $resolver, Frame $frame, Variable $node): void
    {
        $name = $node->getName();
        $variable = $frame->locals()->byName($name)->lastOrNull();
        if (null === $variable) {
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
}
