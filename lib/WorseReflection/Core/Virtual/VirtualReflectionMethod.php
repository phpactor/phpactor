<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Visibility;

class VirtualReflectionMethod extends VirtualReflectionMember implements ReflectionMethod
{
    private Type $type;

    public function __construct(
        ByteOffsetRange $position,
        ReflectionClassLike $declaringClass,
        ReflectionClassLike $class,
        string $name,
        Frame $frame,
        DocBlock $docblock,
        ReflectionScope $scope,
        Visibility $visiblity,
        Type $inferredType,
        Type $type,
        private ReflectionParameterCollection $parameters,
        private NodeText $body,
        private bool $isAbstract,
        private bool $isStatic,
        Deprecation $deprecation
    ) {
        parent::__construct($position, $declaringClass, $class, $name, $frame, $docblock, $scope, $visiblity, $inferredType, $type, $deprecation);
    }

    public static function fromReflectionMethod(ReflectionMethod $reflectionMethod): self
    {
        return new self(
            $reflectionMethod->position(),
            $reflectionMethod->declaringClass(),
            $reflectionMethod->class(),
            $reflectionMethod->name(),
            $reflectionMethod->frame(),
            $reflectionMethod->docblock(),
            $reflectionMethod->scope(),
            $reflectionMethod->visibility(),
            $reflectionMethod->inferredType(),
            $reflectionMethod->type(),
            $reflectionMethod->parameters(),
            $reflectionMethod->body(),
            $reflectionMethod->isAbstract(),
            $reflectionMethod->isStatic(),
            $reflectionMethod->deprecation()
        );
    }

    public function parameters(): ReflectionParameterCollection
    {
        return $this->parameters;
    }

    public function body(): NodeText
    {
        return $this->body;
    }

    public function returnType(): Type
    {
        return $this->type();
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function isVirtual(): bool
    {
        return true;
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_METHOD;
    }

    public function withClass(ReflectionClassLike $class): ReflectionMember
    {
        $new = clone $this;
        $new->class = $class;
        return $new;
    }
}
