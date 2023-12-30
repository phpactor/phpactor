<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class EnumCaseType extends ReflectedClassType implements ClassLikeType
{
    public function __construct(ClassReflector $reflector, public ClassType $enumType, public string $caseName)
    {
        parent::__construct($reflector, ClassName::fromString('UnitEnumCase'));
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

    public function map(Closure $mapper): Type
    {
        return new self(
            $this->reflector,
            /** @phpstan-ignore-next-line Should always return a ClassType */
            $mapper($this->enumType),
            $this->caseName
        );
    }

    public function isAugmented(): bool
    {
        return false;
    }
}
