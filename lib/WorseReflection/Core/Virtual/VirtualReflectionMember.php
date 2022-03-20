<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Visibility;

abstract class VirtualReflectionMember implements ReflectionMember
{
    private Position $position;
    
    private ReflectionClassLike $declaringClass;
    
    private ReflectionClassLike $class;
    
    private string $name;
    
    private Frame $frame;
    
    private DocBlock $docblock;
    
    private ReflectionScope $scope;
    
    private Visibility $visibility;
    
    private Types $inferredTypes;
    
    private Type $type;
    
    private Visibility $visiblity;
    
    private Deprecation $deprecation;

    public function __construct(
        Position $position,
        ReflectionClassLike $declaringClass,
        ReflectionClassLike $class,
        string $name,
        Frame $frame,
        DocBlock $docblock,
        ReflectionScope $scope,
        Visibility $visiblity,
        Types $inferredTypes,
        Type $type,
        Deprecation $deprecation
    ) {
        $this->position = $position;
        $this->declaringClass = $declaringClass;
        $this->class = $class;
        $this->name = $name;
        $this->frame = $frame;
        $this->docblock = $docblock;
        $this->scope = $scope;
        $this->visibility = $visiblity;
        $this->inferredTypes = $inferredTypes;
        $this->type = $type;
        $this->visiblity = $visiblity;
        $this->deprecation = $deprecation;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function declaringClass(): ReflectionClassLike
    {
        return $this->declaringClass;
    }

    public function withDeclaringClass(ReflectionClassLike $contextClass): self
    {
        $new = clone $this;
        $new->declaringClass = $contextClass;
        return $new;
    }

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

    public function withName(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function withInferredTypes(Types $types): self
    {
        $new = clone $this;
        $new->inferredTypes = $types;

        return $new;
    }

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

    public function inferredTypes(): Types
    {
        return $this->inferredTypes;
    }

    public function type(): Type
    {
        return $this->type;
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
