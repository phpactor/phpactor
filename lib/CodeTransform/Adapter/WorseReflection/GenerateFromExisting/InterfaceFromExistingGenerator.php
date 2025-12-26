<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\GenerateFromExisting;

use Phpactor\CodeTransform\Domain\GenerateFromExisting;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\ClassName as ReflectionClassName;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\WorseReflection\Core\Visibility;

final class InterfaceFromExistingGenerator implements GenerateFromExisting
{
    public function __construct(
        private readonly Reflector $reflector,
        private readonly Renderer $renderer
    ) {
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

            if ($method->returnType()->isDefined()) {
                $methodBuilder->returnType($method->returnType()->short(), $method->returnType());

                foreach ($method->returnType()->allTypes()->classLike() as $classType) {
                    $sourceBuilder->use($classType->toPhpString());
                }
            }

            /** @var ReflectionParameter $parameter */
            foreach ($method->parameters() as $parameter) {
                $parameterBuilder = $methodBuilder->parameter($parameter->name());
                $parameterType = $parameter->type();

                if ($parameter->type()->isDefined()) {
                    $parameterBuilder->type($parameterType->short());

                    foreach ($parameterType->allTypes()->classLike() as $classType) {
                        $useClasses[$classType->name()->__toString()] = true;
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
