<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\Resolver\IterableTypeResolver;

class ReflectedClassType extends ClassType
{
    public ClassName $name;

    private ClassReflector $reflector;

    public function __construct(ClassReflector $reflector, ClassName $name)
    {
        $this->name = $name;
        $this->reflector = $reflector;
    }

    public function __toString(): string
    {
        return $this->name->full();
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }

    public function accepts(Type $type): Trinary
    {
        if (!$type instanceof ClassType) {
            return Trinary::false();
        }

        try {
            $reflected = $this->reflector->reflectClass($this->name());
        } catch (NotFound $e) {
            return Trinary::maybe();
        }

        return Trinary::fromBoolean($reflected->isInstanceOf($type->name()));
    }

    public function reflectionOrNull(): ?ReflectionClassLike
    {
        try {
            return $this->reflector->reflectClassLike($this->name());
        } catch (NotFound $notFound) {
        }
        return null;
    }

    public function iterableValueType(): Type
    {
        $class = $this->reflectionOrNull();
        if (null === $class) {
            return new MissingType();
        }

        assert($class instanceof ReflectionClassLike);
        $implements = $class->docblock()->implements();
        $extendsType = $class->docblock()->extends();
        $implements[] = $extendsType;

        foreach ($implements as $implementsType) {
            if (!$implementsType instanceof GenericClassType) {
                return new MissingType();
            }

            return IterableTypeResolver::resolveIterable($implementsType, $implementsType->arguments());
        }

        return new MissingType();
    }


    public function instanceOf(ClassName $className): Trinary
    {
        $reflection = $this->reflectionOrNull();
        if (!$reflection) {
            return Trinary::maybe();
        }
        return Trinary::fromBoolean($reflection->isInstanceOf($className));
    }
}
