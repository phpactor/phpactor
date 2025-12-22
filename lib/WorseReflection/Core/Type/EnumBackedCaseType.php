<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class EnumBackedCaseType extends EnumCaseType implements ClassLikeType
{
    public function __construct(
        ClassReflector $reflector,
        ClassType $enumType,
        string $name,
        public Type $value
    ) {
        parent::__construct($reflector, $enumType, $name);
    }

    public function __toString(): string
    {
        return sprintf('enum(%s::%s)', $this->enumType, $this->caseName);
    }

    public function short(): string
    {
        return $this->enumType->short();
    }

    public function toPhpString(): string
    {
        return $this->enumType;
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
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

    public function isAugmented(): bool
    {
        return false;
    }

    public function map(Closure $mapper): Type
    {
        return new self(
            $this->reflector,
            /** @phpstan-ignore-next-line Should always return a ClassType */
            $mapper($this->enumType),
            $this->caseName,
            $mapper($this->value)
        );
    }
}
