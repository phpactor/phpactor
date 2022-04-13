<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;

class VirtualReflectionParameter implements ReflectionParameter
{
    private string $name;
    
    private ReflectionFunctionLike $functionLike;
    
    private Type $inferredType;
    
    private Type $type;
    
    private DefaultValue $default;
    
    private bool $byReference;
    
    private ReflectionScope $scope;
    
    private Position $position;

    public function __construct(
        string $name,
        ReflectionFunctionLike $functionLike,
        Type $inferredType,
        Type $type,
        DefaultValue $default,
        bool $byReference,
        ReflectionScope $scope,
        Position $position
    ) {
        $this->name = $name;
        $this->functionLike = $functionLike;
        $this->inferredType = $inferredType;
        $this->type = $type;
        $this->default = $default;
        $this->byReference = $byReference;
        $this->scope = $scope;
        $this->position = $position;
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function name(): string
    {
        return $this->name;
    }
    
    public function method(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function functionLike(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function inferredType(): Type
    {
        return $this->inferredType;
    }

    public function default(): DefaultValue
    {
        return $this->default;
    }

    public function byReference(): bool
    {
        return $this->byReference;
    }

    public function isPromoted(): bool
    {
        return false;
    }

    public function isVariadic(): bool
    {
        return false;
    }
}
