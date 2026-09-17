<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Collects method calls (MemberAccessExpression) and function calls
 * (CallExpression) that fall within a given byte-offset range (the body of a
 * method or function).
 */
class OutgoingCallsWalker implements Walker
{
    /**
     * @var list<array{node: Node, name: string, kind: string, offset: int}>
     */
    private array $calls = [];

    public function __construct(private ByteOffsetRange $bodyRange)
    {
    }

    public function nodeFqns(): array
    {
        return [
            MemberAccessExpression::class,
            CallExpression::class,
        ];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if ($node instanceof MemberAccessExpression) {
            $this->collectMemberAccess($node);
        }

        if ($node instanceof CallExpression) {
            $this->collectFunctionCall($node);
        }

        return $frame;
    }

    /**
     * @return list<array{node: Node, name: string, kind: string, offset: int}>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    private function collectMemberAccess(MemberAccessExpression $node): void
    {
        $name = NodeUtil::nameFromTokenOrNode($node, $node->memberName);
        if ($name === '' || !$this->inRange($node)) {
            return;
        }

        $this->calls[] = [
            'node' => $node,
            'name' => $name,
            'kind' => 'method',
            'offset' => $node->memberName instanceof Token ? $node->memberName->getStartPosition() : $node->getStartPosition(),
        ];
    }

    private function collectFunctionCall(CallExpression $node): void
    {
        $name = NodeUtil::nameFromTokenOrNode($node, $node->callableExpression);
        if ($name === '' || !$this->inRange($node)) {
            return;
        }

        $this->calls[] = [
            'node' => $node,
            'name' => $name,
            'kind' => 'function',
            'offset' => $node->callableExpression instanceof Node ? $node->callableExpression->getStartPosition() : $node->getStartPosition(),
        ];
    }

    private function inRange(Node $node): bool
    {
        $start = $node->getStartPosition();
        $end = $node->getEndPosition();

        return $start >= $this->bodyRange->start()->toInt() && $end <= $this->bodyRange->end()->toInt();
    }
}
