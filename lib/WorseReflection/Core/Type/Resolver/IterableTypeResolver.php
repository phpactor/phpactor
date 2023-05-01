<?php

namespace Phpactor\WorseReflection\Core\Type\Resolver;

use Phpactor\WorseReflection\Core\ClassName;

use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

class IterableTypeResolver
{
    public static function iterableClasses(): array
    {
        return [
            'IteratorAggregate',
            'Iterator',
            'Traversable',
            'iterable',
        ];
    }
    /**
     * @param Type[] $arguments
     */
    public static function resolveIterable(ClassReflector $reflector, Type $type, array $arguments): Type
    {
        $genericMapResolver = new GenericMapResolver($reflector);

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

        $iterableClasses = self::iterableClasses();

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

            $templateMap = $genericMapResolver->resolveClassTemplateMap(
                $class->type(),
                ClassName::fromString($iterableClassName),
                $type instanceof GenericClassType ? $type->arguments() : []
            );

            if (null !== $templateMap) {
                $value = $templateMap->get('TValue');
                if (!$value->isDefined()) {
                    return $templateMap->get('TKey');
                }
                return $value;
            }
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
