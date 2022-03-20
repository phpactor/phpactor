<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall as CoreReflectionMethodCall;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;

abstract class AbstractReflectionMethodCall implements CoreReflectionMethodCall
{
    private Frame $frame;

    /**
     * @var ScopedPropertyAccessExpression|MemberAccessExpression
     */
    private $node;
    
    private ServiceLocator $services;

    public function __construct(
        ServiceLocator $services,
        Frame $frame,
        Node $node
    ) {
        $this->services = $services;
        $this->frame = $frame;
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

    public function class(): ReflectionClassLike
    {
        $info = $this->services->symbolContextResolver()->resolveNode($this->frame, $this->node);

        if (!$info->containerType()) {
            throw new CouldNotResolveNode(sprintf(
                'Class for member "%s" could not be determined',
                $this->name()
            ));
        }

        return $this->services->reflector()->reflectClassLike((string) $info->containerType());
    }

    abstract public function isStatic(): bool;

    public function arguments(): ReflectionArgumentCollection
    {
        if (null === $this->callExpression()->argumentExpressionList) {
            return ReflectionArgumentCollection::empty($this->services);
        }

        return ReflectionArgumentCollection::fromArgumentListAndFrame(
            $this->services,
            $this->callExpression()->argumentExpressionList,
            $this->frame
        );
    }

    public function name(): string
    {
        return $this->node->memberName->getText($this->node->getFileContents());
    }

    private function callExpression(): CallExpression
    {
        return $this->node->parent;
    }
}
