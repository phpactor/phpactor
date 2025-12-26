<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall as CoreReflectionMethodCall;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use RuntimeException;

abstract class AbstractReflectionMethodCall implements CoreReflectionMethodCall
{
    /**
     * @param ScopedPropertyAccessExpression|MemberAccessExpression $node
     */
    public function __construct(
        private readonly ServiceLocator $services,
        private readonly Frame $frame,
        private readonly Node $node
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

    abstract public function isStatic(): bool;

    public function arguments(): ReflectionArgumentCollection
    {
        if (null === $this->callExpression()->argumentExpressionList) {
            return ReflectionArgumentCollection::empty();
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


    public function method(): ReflectionMethod
    {
        $class = $this->class();
        return $class->methods()->get($this->name());
    }

    public function inferredReturnType(): Type
    {
        $return = $this->node->getFirstAncestor(ReturnStatement::class);
        if ($return) {
            $functionLike = $this->containingFunctionLike();
            if (null === $functionLike) {
                return new MissingType();
            }
            return $this->class()->scope()->resolveLocalType($functionLike->inferredType());
        }

        return new MissingType();
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

    private function callExpression(): CallExpression
    {
        if (!$this->node->parent instanceof CallExpression) {
            throw new RuntimeException('Method call is not a child of a call expression');
        }

        return $this->node->parent;
    }

    private function containingFunctionLike(): ?ReflectionFunctionLike
    {
        $method = $this->node->getFirstAncestor(MethodDeclaration::class);
        if ($method instanceof MethodDeclaration) {
            $class = $this->class();
            if ($class instanceof ReflectionClass) {
                try {
                    return $class->methods()->get($method->getName());
                } catch (ItemNotFound) {
                }
            }
        }

        return null;
    }
}
