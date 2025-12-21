<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class GlobbedConstantUnionType extends Type
{
    public function __construct(
        private Type $classType,
        private string $glob
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s::%s', $this->classType->__toString(), $this->glob);
    }

    public function toPhpString(): string
    {
        return new MissingType();
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }

    public function toUnion(): Type
    {
        if (!$this->classType instanceof ReflectedClassType) {
            return new MissingType();
        }

        $reflection = $this->classType->reflectionOrNull();

        if (null === $reflection) {
            return new MissingType();
        }

        $types = [];
        foreach ($reflection->members()->byMemberType(ReflectionMember::TYPE_CONSTANT) as $constant) {
            $pattern = preg_quote(str_replace('*', '__ASTERISK__', $this->glob));
            $pattern = str_replace('__ASTERISK__', '.*', $pattern);
            if (preg_match('{' . $pattern . '}', $constant->name())) {
                $types[] = $constant->type();
            }
        }

        return (new UnionType(...$types))->reduce();
    }

    public function map(Closure $mapper): Type
    {
        return new self($mapper($this->classType), $this->glob);
    }
}
