<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Collects outgoing calls (method calls and function calls) that fall within
 * a given byte-offset range (the body of a method or function). A method
 * call is a CallExpression whose callable is a MemberAccessExpression; a
 * function call is a CallExpression whose callable is a plain name.
 */
class OutgoingCallsWalker implements Walker
{
    /**
     * @var list<Call>
     */
    private array $calls = [];

    public function __construct(private ByteOffsetRange $bodyRange)
    {
    }

    public function nodeFqns(): array
    {
        return [
            CallExpression::class,
        ];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if ($node instanceof CallExpression) {
            $this->collectCall($node);
        }

        return $frame;
    }

    /**
     * @return list<Call>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    private function collectCall(CallExpression $node): void
    {
        $callable = $node->callableExpression;
        $isMethod = $callable instanceof MemberAccessExpression;
        $name = NodeUtil::nameFromTokenOrNode($node, $callable);
        if ($name === '' || !$this->inRange($node)) {
            return;
        }

        $this->calls[] = new Call(
            $node,
            $name,
            $isMethod ? 'method' : 'function',
            $callable->getStartPosition(),
        );
    }

    private function inRange(Node $node): bool
    {
        $start = $node->getStartPosition();
        $end = $node->getEndPosition();

        return $start >= $this->bodyRange->start()->toInt() && $end <= $this->bodyRange->end()->toInt();
    }
}
