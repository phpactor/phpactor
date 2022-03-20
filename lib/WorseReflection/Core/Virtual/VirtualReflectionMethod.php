<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Visibility;

class VirtualReflectionMethod extends VirtualReflectionMember implements ReflectionMethod
{
    private ReflectionParameterCollection $parameters;
    
    private NodeText $body;
    
    private Type $type;
    
    private bool $isAbstract;
    
    private bool $isStatic;

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
        ReflectionParameterCollection $parameters,
        NodeText $body,
        bool $isAbstract,
        bool $isStatic,
        Deprecation $deprecation
    ) {
        parent::__construct($position, $declaringClass, $class, $name, $frame, $docblock, $scope, $visiblity, $inferredTypes, $type, $deprecation);
        $this->body = $body;
        $this->parameters = $parameters;
        $this->isAbstract = $isAbstract;
        $this->isStatic = $isStatic;
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
            $reflectionMethod->inferredTypes(),
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
        return ReflectionMember::TYPE_PROPERTY;
    }
}
