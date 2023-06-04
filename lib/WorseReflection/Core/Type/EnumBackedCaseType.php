<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;

class EnumBackedCaseType extends Type implements ClassLikeType
{
    public function __construct(private ClassReflector $reflector, public ClassType $enumType, public string $name, public Type $value)
    {
    }

    public function __toString(): string
    {
        return sprintf('%s::%s', $this->enumType, $this->name);
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
        return $members->merge($case->members()->properties());
    }
}
