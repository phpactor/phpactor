<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\LanguageServerProtocol\InlayHintKind;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Context\ClassLikeContext;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class InlayHintWalker implements Walker
{
    /**
     * @var InlayHint[]
     */
    private array $hints = [];

    public function __construct(
        private ByteOffsetRange $range,
        private InlayHintOptions $options
    ) {
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
        if ($this->options->types && $node instanceof Variable) {
            $this->fromVariable($resolver, $frame, $node);
        }
        if ($this->options->types && $node instanceof ClassLike) {
            $this->fromClassLike($resolver, $frame, $node);
        }
        if ($this->options->types && $node instanceof FunctionLike) {
            $this->fromFunctionLike($resolver, $frame, $node);
        }
        if ($this->options->params && $node instanceof CallExpression) {
            $this->fromCall($resolver, $frame, $node);
        }
        if ($this->options->params && $node instanceof ObjectCreationExpression) {
            $this->fromObjectCreation($resolver, $frame, $node);
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
        })($resolver->resolveNode($frame, $node));

        if (null === $parameters) {
            return;
        }

        foreach ($node->argumentExpressionList?->getValues() ?? [] as $index => $argument) {
            if (!$argument instanceof ArgumentExpression) {
                continue;
            }
            if ($argument->name) {
                continue;
            }
            $parameter = $parameters->at($index);
            if (null === $parameter) {
                break;
            }

            if ($argument->expression instanceof Variable) {
                $name = NodeUtil::nameFromTokenOrNode($argument->expression, $argument->expression->name);
                if (ltrim($name, '$') === $parameter->name()) {
                    continue;
                }
            }
            $this->hints[] = new InlayHint(
                position: PositionConverter::intByteOffsetToPosition($argument->getStartPosition(), $node->getFileContents()),
                label: sprintf('%s:', $parameter->name()),
                kind: InlayHintKind::PARAMETER,
                textEdits: null,
                tooltip: $parameter->type()->__toString(),
                paddingRight: true,
            );
        }
    }

    private function fromVariable(FrameResolver $resolver, Frame $frame, Variable $node): void
    {
        $name = $node->getName();
        if (!$node->parent instanceof AssignmentExpression) {
            return;
        }

        if (null === $name) {
            return;
        }

        $variable = $resolver->resolveNode($frame, $node);

        if (false === $variable->type()->isDefined()) {
            return;
        }

        $this->hints[] = new InlayHint(
            position: PositionConverter::intByteOffsetToPosition(
                $node->getEndPosition(),
                $node->getFileContents()
            ),
            label: sprintf(': %s', $variable->type()->short()),
            tooltip: $variable->type()->__toString(),
            kind: InlayHintKind::TYPE,
            textEdits: null,
        );
    }

    private function fromObjectCreation(FrameResolver $resolver, Frame $frame, ObjectCreationExpression $node): void
    {
        $context = $resolver->resolveNode($frame, $node);
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
            if ($argument->name !== null) {
                continue;
            }
            $parameter = $parameters->at($index);
            if (null === $parameter) {
                break;
            }
            $this->hints[] = new InlayHint(
                position: PositionConverter::intByteOffsetToPosition($argument->getStartPosition(), $node->getFileContents()),
                label: sprintf('%s:', $parameter->name()),
                kind: InlayHintKind::PARAMETER,
                textEdits: null,
                tooltip: $parameter->type()->__toString(),
                paddingRight: true,
            );
        }
    }

    private function fromClassLike(FrameResolver $resolver, Frame $frame, Node&ClassLike $node): void
    {
        if (!$node instanceof ClassDeclaration && !$node instanceof EnumDeclaration && !$node instanceof TraitDeclaration && !$node instanceof InterfaceDeclaration) {
            return;
        }
        $context = $resolver->resolveNode($frame, $node);

        $this->hints[] = new InlayHint(
            position: PositionConverter::intByteOffsetToPosition($node->getEndPosition(), $node->getFileContents()),
            label: sprintf('%s %s', $context->symbol()->symbolType(), $context->symbol()->name()),
            kind: InlayHintKind::TYPE,
            textEdits: null,
            paddingLeft: true,
        );
    }

    private function fromFunctionLike(FrameResolver $resolver, Frame $frame, Node&FunctionLike $node): void
    {
        if (!$node instanceof MethodDeclaration && !$node instanceof FunctionDeclaration) {
            return;
        }
        $name = NodeUtil::nameFromTokenOrNode($node, $node->name);
        $type = $node instanceof MethodDeclaration ? 'method' : 'function';

        $this->hints[] = new InlayHint(
            position: PositionConverter::intByteOffsetToPosition($node->getEndPosition(), $node->getFileContents()),
            label: sprintf('%s %s', $type, $name),
            kind: InlayHintKind::TYPE,
            textEdits: null,
            paddingLeft: true,
        );
    }
}
