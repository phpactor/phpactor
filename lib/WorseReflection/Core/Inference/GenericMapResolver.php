<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class GenericMapResolver
{
    private ClassReflector $reflector;

    public function __construct(ClassReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     * @param Type[] $arguments
     */
    public function resolveClassTemplateMap(Type $topClass, ClassName $bottomClass, array $arguments = []): ?TemplateMap
    {
        if (!$topClass instanceof ClassType) {
            return null;
        }

        $topReflection = $this->reflector->reflectClassLike($topClass->name());

        $templateMap = $topReflection->templateMap();
        $templateMap = $templateMap->mapArguments($arguments);

        if ($topClass->name() == $bottomClass) {
            return $templateMap;
        }

        foreach (array_merge(
            $topReflection->docblock()->implements(),
            $topReflection->docblock()->extends()
        ) as $genericClass
        ) {
            if (!$genericClass instanceof GenericClassType) {
                continue;
            }

            $genericClass = $genericClass->map(function (Type $type) use ($templateMap) {
                if ($templateMap->has($type->short())) {
                    return $templateMap->get($type->short());
                }
                return $type;
            });

            if (!$genericClass instanceof GenericClassType) {
                // should not happen
                continue;
            }

            if (null !== $resolved = $this->resolveClassTemplateMap($genericClass, $bottomClass, $genericClass->arguments())) {
                return $resolved;
            }
        }

        return null;
    }

    public function mergeParameters(TemplateMap $templateMap, ReflectionParameterCollection $parameters, FunctionArguments $arguments): TemplateMap
    {
        foreach ($parameters as $parameter) {
            $parameterType = $parameter->inferredType();
            if ($parameterType instanceof ClassStringType && $parameterType->className()) {
                $this->mapClassString($parameterType, $templateMap, $arguments, $parameter);
                return $templateMap;
            }
            $parameterType->map(function (Type $type) use ($parameter, $templateMap, $arguments) {
                if ($type instanceof ClassStringType && $type->className()) {
                    $this->mapClassString($type, $templateMap, $arguments, $parameter);
                    return $type;
                }

                if ($templateMap->has($type->short())) {
                    $templateMap->replace($type->short(), $arguments->at($parameter->index())->type()->generalize());
                }

                return $type;
            });
        }
        return $templateMap;
    }

    private function mapClassString(ClassStringType $type, TemplateMap $templateMap, FunctionArguments $arguments, ReflectionParameter $parameter): void
    {
        $argument = $arguments->at($parameter->index())->type();
        if (!$argument->isDefined()) {
            return;
        }
        $classStringType = $type->className()->short();
        if ($templateMap->has($classStringType)) {
            if ($argument instanceof ClassStringType) {
                $templateMap->replace($classStringType, TypeFactory::reflectedClass($this->reflector, $argument->className()));
            }
            if ($argument instanceof StringLiteralType) {
                $templateMap->replace($classStringType, TypeFactory::reflectedClass($this->reflector, $argument->value()));
            }

            return;
        }
    }
}
