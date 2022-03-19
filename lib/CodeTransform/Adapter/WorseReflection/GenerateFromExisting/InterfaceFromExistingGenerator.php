<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\GenerateFromExisting;

use Phpactor\CodeTransform\Domain\GenerateFromExisting;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\ClassName as ReflectionClassName;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\TypeUtil;

final class InterfaceFromExistingGenerator implements GenerateFromExisting
{
    private Reflector $reflector;

    private Renderer $renderer;

    public function __construct(Reflector $reflector, Renderer $renderer)
    {
        $this->reflector = $reflector;
        $this->renderer = $renderer;
    }

    
    public function generateFromExisting(ClassName $existingClass, ClassName $targetName): SourceCode
    {
        $existingClass = $this->reflector->reflectClass(ReflectionClassName::fromString((string) $existingClass));

        /** @var SourceCodeBuilder $sourceBuilder */
        $sourceBuilder = SourceCodeBuilder::create();
        $sourceBuilder->namespace($targetName->namespace());
        $interfaceBuilder = $sourceBuilder->interface($targetName->short());
        $useClasses = [];

        /** @var ReflectionMethod $method */
        foreach ($existingClass->methods()->byVisibilities([ Visibility::public() ]) as $method) {
            if ($method->name() === '__construct') {
                continue;
            }

            $methodBuilder = $interfaceBuilder->method($method->name());
            $methodBuilder->visibility((string) $method->visibility());

            if ($method->docblock()->isDefined()) {
                $methodBuilder->docblock($method->docblock()->formatted());
            }

            if (TypeUtil::isDefined($method->returnType())) {
                $methodBuilder->returnType(TypeUtil::short($method->returnType()));

                foreach (TypeUtil::unwrapClassTypes($method->returnType()) as $classType) {
                    $sourceBuilder->use($classType->toPhpString());
                }
            }

            /** @var ReflectionParameter $parameter */
            foreach ($method->parameters() as $parameter) {
                $parameterBuilder = $methodBuilder->parameter($parameter->name());
                $parameterType = $parameter->type();

                if (TypeUtil::isDefined($parameter->type())) {
                    $parameterBuilder->type(TypeUtil::short($parameterType));

                    $parameterType = TypeUtil::unwrapNullableType($parameterType);
                    if ($parameterType instanceof ClassType) {
                        $useClasses[$parameterType->name->__toString()] = true;
                    }

                    if ($parameter->default()->isDefined()) {
                        $parameterBuilder->defaultValue($parameter->default()->value());
                    }
                }
            }
        }

        foreach (array_keys($useClasses) as $useClass) {
            $sourceBuilder->use($useClass);
        }

        return SourceCode::fromString($this->renderer->render($sourceBuilder->build()));
    }
}
