<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\SourceCode;

class VirtualReflectionClassLikeDecorator implements ReflectionClassLike
{
    private ReflectionClassLike $classLike;

    public function __construct(ReflectionClassLike $classLike)
    {
        $this->classLike = $classLike;
    }

    public function scope(): ReflectionScope
    {
        return $this->classLike->scope();
    }

    public function position(): Position
    {
        return $this->classLike->position();
    }

    public function name(): ClassName
    {
        return $this->classLike->name();
    }

    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection
    {
        return $this->classLike->methods();
    }

    public function members(): ReflectionMemberCollection
    {
        return $this->classLike->members();
    }

    public function sourceCode(): SourceCode
    {
        return $this->classLike->sourceCode();
    }

    public function isInterface(): bool
    {
        return $this->classLike->isInterface();
    }

    public function isInstanceOf(ClassName $className): bool
    {
        return $this->classLike->isInstanceOf($className);
    }

    public function isTrait(): bool
    {
        return $this->classLike->isTrait();
    }

    public function isEnum(): bool
    {
        return $this->classLike->isEnum();
    }

    public function isClass(): bool
    {
        return $this->classLike->isClass();
    }

    public function isConcrete()
    {
        return $this->classLike->isConcrete();
    }

    public function docblock(): DocBlock
    {
        return $this->classLike->docblock();
    }

    public function deprecation(): Deprecation
    {
        return $this->classLike->deprecation();
    }
}
