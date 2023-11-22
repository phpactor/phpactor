<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MatchExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReflectionMatchExpression implements ReflectionNode
{
    /**
     * @param MatchExpression $node
     */
    public function __construct(
        private ServiceLocator $services,
        private Frame $frame,
        private Node $node
    ) {
    }

    public function position(): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts(
            $this->node->getStartPosition(),
            $this->node->getEndPosition()
        );
    }

    public function expressionType(): Type
    {
        $expr = $this->services->nodeContextResolver()->resolveNode($this->frame, $this->node->expression);
        return $expr->type();
    }
    public function scope(): ReflectionScope
    {
        return new ReflectionScope($this->services->reflector(), $this->node);
    }
}
