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
use Phpactor\WorseReflection\Core\Type\UnionType;

class GenericMapResolver
{
    public function __construct(private ClassReflector $reflector)
    {
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
            $paramTypes = $parameterType->allTypes();
            $argumentTypes = $arguments->at($parameter->index())->type()->allTypes();

            foreach ($paramTypes as $index => $paramType) {
                $argumentType = $argumentTypes->at($index);

                if ($paramType instanceof ClassStringType && $paramType->className()) {
                    $this->mapClassString($paramType, $templateMap, $arguments, $parameter);
                }

                if ($templateMap->has($paramType->short())) {
                    $templateMap->replace(
                        $paramType->short(),
                        $argumentType->generalize()
                    );
                }
            }
        }
        return $templateMap;
    }

    private function mapClassString(ClassStringType $type, TemplateMap $templateMap, FunctionArguments $arguments, ReflectionParameter $parameter): void
    {
        $classStringType = $type->className()->short();
        if (!$templateMap->has($classStringType)) {
            return;
        }
        if ($parameter->isVariadic()) {
            $arguments = $arguments->from($parameter->index());
        } else {
            $arguments = [$arguments->at($parameter->index())];
        }

        $types = [];
        foreach ($arguments as $index => $argument) {
            $argumentType = $argument->type();
            if ($argumentType instanceof ClassStringType) {
                $className = $argumentType->className();
                if (null === $className) {
                    continue;
                }
                $types[] = TypeFactory::reflectedClass($this->reflector, $className);
            }

            if ($argumentType instanceof StringLiteralType) {
                $types[] = TypeFactory::reflectedClass($this->reflector, $argumentType->value());
            }

            if ($types) {
                $templateMap->replace($classStringType, UnionType::fromTypes(...$types));
            }
        }
    }
}
