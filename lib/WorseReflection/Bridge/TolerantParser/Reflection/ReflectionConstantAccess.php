<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReflectionConstantAccess
{
    /**
     * @param ScopedPropertyAccessExpression|MemberAccessExpression $node
     */
    public function __construct(private Node $node)
    {
    }

    public function position(): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts(
            $this->node->getStartPosition(),
            $this->node->getEndPosition()
        );
    }

    public function name(): string
    {
        return NodeUtil::nameFromTokenOrNode($this->node, $this->node->memberName);
    }

    public function nameRange(): ByteOffsetRange
    {
        $memberName = $this->node->memberName;
        return ByteOffsetRange::fromInts(
            $memberName->getStartPosition(),
            $memberName->getEndPosition()
        );
    }
}
