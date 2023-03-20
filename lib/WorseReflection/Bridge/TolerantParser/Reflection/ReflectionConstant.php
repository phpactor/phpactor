<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant as CoreReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TypeFactory;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Phpactor\WorseReflection\TypeUtil;

class ReflectionConstant extends AbstractReflectionClassMember implements CoreReflectionConstant
{
    public function __construct(
        private ServiceLocator $serviceLocator,
        private ReflectionClassLike $class,
        private ClassConstDeclaration $declaration,
        private ConstElement $node
    ) {
    }

    public function name(): string
    {
        return (string)$this->node->getName();
    }

    public function nameRange(): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts(
            $this->node->name->getStartPosition(),
            $this->node->name->getEndPosition()
        );
    }

    public function type(): Type
    {
        $value = $this->serviceLocator->nodeContextResolver()->resolveNode(FrameStack::new(), $this->node->assignment);
        return $value->type();
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function inferredType(): Type
    {
        if (TypeFactory::unknown() !== $this->type()) {
            return $this->type();
        }

        return TypeFactory::undefined();
    }

    public function isVirtual(): bool
    {
        return false;
    }

    public function value()
    {
        return TypeUtil::valueOrNull($this->serviceLocator()
            ->nodeContextResolver()
            ->resolveNode(
                new Frame(),
                $this->node->assignment
            )->type());
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_CONSTANT;
    }

    public function withClass(ReflectionClassLike $class): ReflectionMember
    {
        return new self($this->serviceLocator, $class, $this->declaration, $this->node);
    }

    public function isStatic(): bool
    {
        return true;
    }

    protected function node(): Node
    {
        return $this->declaration;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
