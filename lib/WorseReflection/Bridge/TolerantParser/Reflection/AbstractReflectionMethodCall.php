<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall as CoreReflectionMethodCall;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use RuntimeException;

abstract class AbstractReflectionMethodCall implements CoreReflectionMethodCall
{
    private Frame $frame;

    /**
     * @var ScopedPropertyAccessExpression|MemberAccessExpression
     */
    private $node;
    
    private ServiceLocator $services;

    /**
     * @param ScopedPropertyAccessExpression|MemberAccessExpression $node
     */
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

        if (!$info->containerType()->isDefined()) {
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
        return NodeUtil::nameFromTokenOrNode($this->node, $this->node->memberName);
    }


    public function inferredReturnType(): Type
    {
        $return = $this->node->getFirstAncestor(ReturnStatement::class);
        if ($return) {
            $functionLike = $this->functionLike();
            if (null === $functionLike) {
                return new MissingType();
            }
            return $this->class()->scope()->resolveLocalType($functionLike->inferredType());
        }

        return new MissingType();
    }

    public function nameRange(): ByteOffsetRange
    {
        $memberName = $this->node->memberName;
        return ByteOffsetRange::fromInts(
            $memberName->getStartPosition(),
            $memberName->getEndPosition()
        );
    }

    private function callExpression(): CallExpression
    {
        if (!$this->node->parent instanceof CallExpression) {
            throw new RuntimeException('Method call is not a child of a call expression');
        }

        return $this->node->parent;
    }

    private function functionLike(): ?ReflectionFunctionLike
    {
        $method = $this->node->getFirstAncestor(MethodDeclaration::class);
        if ($method instanceof MethodDeclaration) {
            $class = $this->class();
            if ($class instanceof ReflectionClass) {
                try {
                    return $class->methods()->get($method->getName());
                } catch (ItemNotFound $notFound) {
                }
            }
        }

        return null;
    }
}
