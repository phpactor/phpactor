<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ClassLikeReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

class ClassType extends Type implements ClassLikeType, HasEmptyType
{
    /**
     * @var ReflectionMemberCollection<ReflectionMember>
     */
    public ReflectionMemberCollection $members;

    public function __construct(public ClassName $name)
    {
        $this->members = ClassLikeReflectionMemberCollection::empty();
    }

    public function __toString(): string
    {
        return $this->name->full();
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }

    public function name(): ClassName
    {
        return $this->name;
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection
    {
        return $this->members;
    }

    /**
     * @param ReflectionMemberCollection<ReflectionMember> $collection
     */
    public function mergeMembers(ReflectionMemberCollection $collection): self
    {
        $new = clone $this;
        $new->members = $this->members->merge($collection);
        return $new;
    }


    public function is(Type $type): Trinary
    {
        if ($type instanceof MissingType) {
            return Trinary::maybe();
        }

        if (!$type instanceof ClassType) {
            return Trinary::false();
        }

        return Trinary::fromBoolean($type->name() == $this->name());
    }

    public function accepts(Type $type): Trinary
    {
        if ($this->is($type)->isTrue()) {
            return Trinary::true();
        }

        if ($type instanceof ClassType) {
            return Trinary::maybe();
        }

        return Trinary::false();
    }

    public function instanceof(Type $right): Trinary
    {
        if ($right->equals($this)) {
            return Trinary::true();
        }
        return Trinary::maybe();
    }

    public function isInterface(): Trinary
    {
        return Trinary::maybe();
    }

    public function isUnknown(): Trinary
    {
        return Trinary::true();
    }

    public function emptyType(): Type
    {
        return $this;
    }

    public function asReflectedClassType(Reflector $reflector): ReflectedClassType
    {
        if ($this instanceof ReflectedClassType) {
            return $this;
        }
        return new ReflectedClassType($reflector, $this->name());
    }
}
