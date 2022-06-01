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
use Phpactor\WorseReflection\Core\TypeFactory;
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

    public function isInvokable(): bool
    {
        $reflection = $this->reflectionOrNull();
        if (null === $reflection) {
            return false;
        }

        return $reflection->methods()->has('__invoke');
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
        $genericTypes = array_merge($class->docblock()->implements(), $class->docblock()->extends());

        foreach ($genericTypes as $genericType) {
            if (!$genericType instanceof GenericClassType) {
                return new MissingType();
            }

            $type = IterableTypeResolver::resolveIterable($genericType, $genericType->arguments());
            if (!$type->isDefined()) {
                continue;
            }
            return $type;
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

    /**
     * If the class type has a template, then upcast it
     */
    public function upcastToGeneric(): ReflectedClassType
    {
        if ($this instanceof GenericClassType) {
            return $this;
        }
        $reflection = $this->reflectionOrNull();
        if (!$reflection) {
            return $this;
        }

        if (0 === $reflection->templateMap()->count()) {
            return $this;
        }

        return new GenericClassType($this->reflector, $this->name(), $reflection->templateMap()->toArray());
    }

    public function invokeType(): Type
    {
        $reflection = $this->reflectionOrNull();
        if (null === $reflection) {
            return TypeFactory::undefined();
        }

        try {
            return $reflection->methods()->get('__invoke')->inferredType();
        } catch (NotFound $notFound) {
            return TypeFactory::undefined();
        }
    }
}
