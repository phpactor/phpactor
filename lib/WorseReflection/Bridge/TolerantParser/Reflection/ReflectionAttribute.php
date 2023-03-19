<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Attribute;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionObjectCreationExpression as PhpactorReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope as TolerantReflectionScope;

class ReflectionAttribute implements PhpactorReflectionObjectCreationExpression, ClassInvocation
{
    public function __construct(
        private ServiceLocator $locator,
        private Frame $frame,
        private Attribute $node
    ) {
    }

    public function scope(): ReflectionScope
    {
        return new TolerantReflectionScope($this->locator->reflector(), $this->node);
    }

    public function position(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->getFullStartPosition(),
            $this->node->getStartPosition(),
            $this->node->getEndPosition()
        );
    }

    public function class(): ReflectionClassLike
    {
        $type = $this->locator->nodeContextResolver()->resolveNode($this->frame, $this->node->name)->type();

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
