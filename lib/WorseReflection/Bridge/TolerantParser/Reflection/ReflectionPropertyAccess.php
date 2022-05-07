<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Position;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReflectionPropertyAccess
{
    /**
     * @var ScopedPropertyAccessExpression|MemberAccessExpression
     */
    private $node;


    /**
     * @param ScopedPropertyAccessExpression|MemberAccessExpression $node
     */
    public function __construct(
        Node $node
    ) {
        $this->node = $node;
    }

    public function position(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->getFullStartPosition(),
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
