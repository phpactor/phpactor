<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class EnumCaseType extends ReflectedClassType implements ClassNamedType
{
    public ClassType $enumType;

    public string $caseName;

    public function __construct(ClassReflector $reflector, ClassType $enumType, string $caseName)
    {
        parent::__construct($reflector, ClassName::fromString('UnitEnumCase'));
        $this->enumType = $enumType;
        $this->caseName = $caseName;
    }

    public function __toString(): string
    {
        return sprintf('%s::%s', $this->enumType, $this->caseName);
    }

    public function toPhpString(): string
    {
        return $this->enumType;
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }
}
