<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionObjectCreationExpression as PhpactorReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope as TolerantReflectionScope;

class ReflectionObjectCreationExpression implements PhpactorReflectionObjectCreationExpression, ClassInvocation
{
    public function __construct(
        private readonly ServiceLocator $locator,
        private readonly Frame $frame,
        private readonly ObjectCreationExpression $node
    ) {
    }

    public function scope(): ReflectionScope
    {
        return new TolerantReflectionScope($this->locator->reflector(), $this->node);
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
        $type = $this->locator->nodeContextResolver()->resolveNode($this->frame, $this->node->classTypeDesignator)->type();

        if (!$type instanceof ReflectedClassType) {
            throw new CouldNotResolveNode(sprintf('Expceted "%s" but got "%s"', ReflectedClassType::class, get_class($type)));
        }

        $reflection = $type->reflectionOrNull();

        if (null === $reflection) {
            throw new CouldNotResolveNode(
                'Could not reflect class'
            );
        }

        return $reflection;
    }

    public function arguments(): ReflectionArgumentCollection
    {
        if (null === $this->node->argumentExpressionList) {
            return ReflectionArgumentCollection::empty();
        }

        return ReflectionArgumentCollection::fromArgumentListAndFrame(
            $this->locator,
            $this->node->argumentExpressionList,
            $this->frame
        );
    }
}
