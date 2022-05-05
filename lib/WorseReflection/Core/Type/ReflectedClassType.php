<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\Resolver\IterableTypeResolver;

class ReflectedClassType extends ClassType
{
    public ClassName $name;

    protected ClassReflector $reflector;

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

    /**
     * Accept if same class or class extends this class
     */
    public function accepts(Type $type): Trinary
    {
        if ($type->equals($this)) {
            return Trinary::true();
        }

        if (!$type instanceof ClassType) {
            return Trinary::false();
        }

        $reflectedThis = $this->reflectionOrNull();

        if (null === $reflectedThis) {
            return Trinary::maybe();
        }

        try {
            $reflectedThat = $this->reflector->reflectClassLike($type->name());
        } catch (NotFound $e) {
            return Trinary::maybe();
        }

        if ($reflectedThis instanceof ReflectionInterface) {
            return Trinary::fromBoolean($reflectedThat->isInstanceOf($reflectedThis->name()));
        }

        if ($reflectedThat->name() == $this->name()) {
            return Trinary::true();
        }

        if ($reflectedThat instanceof ReflectionClass) {
            while ($parent = $reflectedThat->parent()) {
                if ($parent->name() == $this->name()) {
                    return Trinary::true();
                }
                $reflectedThat = $parent;
            }
        }

        return Trinary::false();
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
        if (!$class instanceof ReflectionClassLike) {
            return new MissingType();
        }
        $scope = $class->scope();

        assert($class instanceof ReflectionClassLike);
        $implements = $class->docblock()->implements();
        $extendsType = $class->docblock()->extends();
        if (($extendsType->isDefined())) {
            $implements[] = $extendsType;
        }

        foreach ($implements as $implementsType) {
            if (!$implementsType instanceof GenericClassType) {
                return new MissingType();
            }

            return IterableTypeResolver::resolveIterable($implementsType, $implementsType->arguments());
        }

        return new MissingType();
    }

    public function instanceof(Type $type): Trinary
    {
        if ($type instanceof MissingType) {
            return Trinary::maybe();
        }

        if (
            !$type instanceof StringType &&
            !$type instanceof ClassType
        ) {
            return Trinary::false();
        }

        $reflection = $this->reflectionOrNull();

        if (!$reflection) {
            return Trinary::maybe();
        }

        if ($type instanceof StringLiteralType) {
            return Trinary::fromBoolean($reflection->isInstanceOf(ClassName::fromString($type->value())));
        }
        if ($type instanceof ClassType) {
            return Trinary::fromBoolean($reflection->isInstanceOf($type->name()));
        }

        return Trinary::maybe();
    }
}
