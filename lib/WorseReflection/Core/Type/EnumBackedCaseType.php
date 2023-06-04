<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class EnumBackedCaseType extends Type implements ClassLikeType
{
    public function __construct(private ClassReflector $reflector, public ClassType $enumType, public string $name, public Type $value)
    {
    }

    public function __toString(): string
    {
        return sprintf('enum(%s::%s)', $this->enumType, $this->name);
    }

    public function toPhpString(): string
    {
        return $this->enumType;
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }

    public function name(): ClassName
    {
        return ClassName::fromString('BackedEnumCase');
    }

    public function members(): ReflectionMemberCollection
    {
        $members = $this->enumType->members();
        try {
            $case = $this->reflector->reflectClass('BackedEnumCase');
        } catch (NotFound) {
            return $members;
        }
        /** @phpstan-ignore-next-line */
        return $members->merge($case->members()->properties());
    }
}
