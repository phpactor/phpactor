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

    /**
     * Accept if same class or class extends this class
     */
    public function accepts(Type $type): Trinary
    {
        if (!$type instanceof ClassType) {
            return Trinary::false();
        }

        try {
            $reflectedThat = $this->reflector->reflectClass($type->name());
        } catch (NotFound $e) {
            return Trinary::maybe();
        }

        if ($reflectedThat->name() == $this->name()) {
            return Trinary::true();
        }

        while ($parent = $reflectedThat->parent()) {
            if ($parent->name() == $this->name()) {
                return Trinary::true();
            }
            $reflectedThat = $parent;
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

    public function instanceof(Type $right): BooleanType
    {
        $resolve = function (Type $right): void {
        };

        if ($right instanceof MissingType) {
            return new BooleanType();
        }

        if (
            !$right instanceof StringType &&
            !$right instanceof ClassType
        ) {
            return new BooleanLiteralType(false);
        }

        $reflection = $this->reflectionOrNull();

        if (!$reflection) {
            return new BooleanType();
        }

        if ($right instanceof StringLiteralType) {
            return new BooleanLiteralType($reflection->isInstanceOf(ClassName::fromString($right->value())));
        }
        if ($right instanceof ClassType) {
            return new BooleanLiteralType($reflection->isInstanceOf($right->name()));
        }

        return new BooleanType();
    }
}
