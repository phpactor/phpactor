<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReflectionStaticMemberAccess implements ReflectionNode
{
    /**
     * @param ScopedPropertyAccessExpression|MemberAccessExpression $node
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

    public function class(): ReflectionClassLike
    {
        $info = $this->services->nodeContextResolver()->resolveNode($this->frame, $this->node);
        $containerType = $info->containerType();

        if (!$containerType instanceof ReflectedClassType) {
            throw new CouldNotResolveNode(sprintf(
                'Class for member "%s" could not be determined',
                $this->name()
            ));
        }

        $reflection = $containerType->reflectionOrNull();

        if (null === $reflection) {
            throw new CouldNotResolveNode(sprintf(
                'Class for member "%s" could not be determined',
                $this->name()
            ));
        }

        return $reflection;
    }

    public function name(): string
    {
        return NodeUtil::nameFromTokenOrNode($this->node, $this->node->memberName);
    }

    public function scope(): ReflectionScope
    {
        return new ReflectionScope($this->services->reflector(), $this->node);
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
