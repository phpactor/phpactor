<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MatchExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class ReflectionCallExpression implements ReflectionNode
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

    public function functionLike(): ReflectionFunctionLike
    {
        $this->
    }

    public function scope(): ReflectionScope
    {
        return new ReflectionScope($this->services->reflector(), $this->node);
    }
}
