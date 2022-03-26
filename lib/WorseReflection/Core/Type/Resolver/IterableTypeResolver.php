<?php

namespace Phpactor\WorseReflection\Core\Type\Resolver;

use Phpactor\WorseReflection\Core\ClassName;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

class IterableTypeResolver
{
    /**
     * @param Type[] $arguments
     */
    public static function resolveIterable(Type $type, array $arguments): Type
    {
        if (!$type instanceof ClassType) {
            return new MissingType();
        }

        if ($type->name()->__toString() === 'Generator') {
            if (count($arguments) === 1) {
                return $arguments[0];
            }

            if (isset($arguments[1])) {
                return $arguments[1];
            }
        }

        $iterableClasses = [
            'Traversable',
            'Iterator',
            'IteratorAggregate',
        ];

        if (in_array($type->name()->__toString(), $iterableClasses)) {
            return self::valueTypeFromArgs($arguments);
        }

        if (!$type instanceof ReflectedClassType) {
            return new MissingType();
        }

        $class = $type->reflectionOrNull();

        if (null === $class) {
            return new MissingType();
        }

        foreach ($iterableClasses as $iterableClassName) {
            if (false === $class->isInstanceOf(ClassName::fromString($iterableClassName))) {
                continue;
            }

            return self::valueTypeFromArgs($arguments);
        }

        return new MissingType();
    }

    /**
     * @param Type[] $arguments
     */
    private static function valueTypeFromArgs(array $arguments): Type
    {
        if (isset($arguments[1])) {
            return $arguments[1];
        }
        if (isset($arguments[0])) {
            return $arguments[0];
        }

        return new MissingType();
    }
}
