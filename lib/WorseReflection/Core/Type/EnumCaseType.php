<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class EnumCaseType extends Type implements ClassNamedType
{
    public ClassType $enumType;
    public string $name;

    public function __construct(ClassType $enumType, string $name)
    {
        $this->enumType = $enumType;
        $this->name = $name;
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
}
