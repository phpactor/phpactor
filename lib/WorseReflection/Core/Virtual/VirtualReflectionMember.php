<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\MemberTypeContextualiser;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Visibility;

abstract class VirtualReflectionMember implements ReflectionMember
{
    private MemberTypeContextualiser $contextualizer;

    public function __construct(
        private ByteOffsetRange $position,
        private ReflectionClassLike $declaringClass,
        protected ReflectionClassLike $class,
        private string $name,
        private Frame $frame,
        private DocBlock $docblock,
        private ReflectionScope $scope,
        private Visibility $visibility,
        private Type $inferredType,
        private Type $type,
        private Deprecation $deprecation
    ) {
        $this->contextualizer = new MemberTypeContextualiser();
    }

    public function position(): ByteOffsetRange
    {
        return $this->position;
    }

    public function declaringClass(): ReflectionClassLike
    {
        return $this->declaringClass;
    }

    /**
     * @return $this
     */
    public function withDeclaringClass(ReflectionClassLike $contextClass): self
    {
        $new = clone $this;
        $new->declaringClass = $contextClass;
        return $new;
    }

    /**
     * @return $this
     */
    public function withVisibility(Visibility $visibility): self
    {
        $new = clone $this;
        $new->visibility = $visibility;
        return $new;
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nameRange(): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts(
            $this->position()->start()->toInt(),
            $this->position()->end()->toInt(),
        );
    }

    /**
     * @return $this
     */
    public function withName(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * @return $this
     */
    public function withInferredType(Type $type): self
    {
        $new = clone $this;
        $new->inferredType = $type;

        return $new;
    }

    /**
     * @return $this
     */
    public function withType(Type $type): self
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function docblock(): DocBlock
    {
        return $this->docblock;
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }

    public function inferredType(): Type
    {
        return $this->contextualizer->contextualise($this->declaringClass, $this->class, $this->inferredType);
    }

    public function type(): Type
    {
        return $this->contextualizer->contextualise($this->declaringClass, $this->class, $this->type);
    }

    public function original(): ReflectionMember
    {
        return $this;
    }

    public function deprecation(): Deprecation
    {
        return $this->deprecation;
    }
}
