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
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Renderer
     */
    private $renderer;

    public function __construct(Reflector $reflector, Renderer $renderer)
    {
        $this->reflector = $reflector;
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
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
                $methodBuilder->returnType($method->returnType()->short());

                if ($method->returnType()->isClass()) {
                    $sourceBuilder->use($method->returnType());
                }
            }

            /** @var ReflectionParameter $parameter */
            foreach ($method->parameters() as $parameter) {
                $parameterBuilder = $methodBuilder->parameter($parameter->name());
                if ($parameter->type()->isDefined()) {
                    if ($parameter->type()->isPrimitive()) {
                        $parameterBuilder->type($parameter->type()->primitive());
                    } else {
                        $className = $parameter->type()->className();
                        if ($className) {
                            $parameterBuilder->type($className->short());
                            $paramClassName = $parameter->type()->className();
                            if ($paramClassName) {
                                $useClasses[$paramClassName->__toString()] = true;
                            }
                        }
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
