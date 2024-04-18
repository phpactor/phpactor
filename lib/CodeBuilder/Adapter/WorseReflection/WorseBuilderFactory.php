<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Builder\ClassLikeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\TraitBuilder;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Reflector;

class WorseBuilderFactory implements BuilderFactory
{
    public function __construct(private Reflector $reflector)
    {
    }

    public function fromSource(TextDocument|string $source): SourceCodeBuilder
    {
        if (!$source instanceof TextDocument) {
            $source = TextDocumentBuilder::create($source)
                ->language('php')
                ->build();
        }

        $classes = $this->reflector->reflectClassLikesIn($source);
        $builder = SourceCodeBuilder::create();

        foreach ($classes as $classLike) {
            if ($classLike instanceof ReflectionClass) {
                $this->build('class', $builder, $classLike);
                continue;
            }

            if ($classLike instanceof ReflectionInterface) {
                $this->build('interface', $builder, $classLike);
                continue;
            }

            if ($classLike instanceof ReflectionTrait) {
                $this->build('trait', $builder, $classLike);
                continue;
            }

            if ($classLike instanceof ReflectionEnum) {
                $this->build('enum', $builder, $classLike);
            }
        }

        $builder->snapshot();

        return $builder;
    }

    private function build(string $type, SourceCodeBuilder $builder, ReflectionClassLike $reflectionClass): void
    {
        $classBuilder = $builder->$type($reflectionClass->name()->short());
        $builder->namespace($reflectionClass->name()->namespace());

        if ($reflectionClass instanceof ReflectionClass || $reflectionClass instanceof ReflectionTrait) {
            foreach ($reflectionClass->properties()->belongingTo($reflectionClass->name()) as $property) {
                assert($property instanceof ReflectionProperty);
                if ($property->isPromoted()) {
                    continue;
                }
                $this->buildProperty($classBuilder, $property);
            }
        }

        foreach ($reflectionClass->methods()->real()->belongingTo($reflectionClass->name()) as $method) {
            $this->buildMethod($classBuilder, $method);
        }
    }

    private function buildProperty(ClassLikeBuilder $classBuilder, ReflectionProperty $property): void
    {
        assert($classBuilder instanceof ClassBuilder || $classBuilder instanceof TraitBuilder);

        $propertyBuilder = $classBuilder->property($property->name());
        $propertyBuilder->visibility((string) $property->visibility());

        $type = $property->inferredType();
        if ($type->isDefined()) {
            $this->importClassesForMemberType($classBuilder, $property->class()->name(), $type);
            $propertyBuilder->type($type->short(), $type);
            $propertyBuilder->docType((string)$type);
        }
    }

    private function buildMethod(ClassLikeBuilder $classBuilder, ReflectionMethod $method): void
    {
        $methodBuilder = $classBuilder->method($method->name());
        $methodBuilder->visibility((string) $method->visibility());

        if ($method->returnType()->isDefined()) {
            $type = $method->returnType();
            $this->importClassesForMemberType($classBuilder, $method->class()->name(), $type);
            $typeName = $type->short();
            $methodBuilder->returnType($typeName, $type);
        }

        if ($method->isStatic()) {
            $methodBuilder->static();
        }

        foreach ($method->parameters() as $parameter) {
            $this->buildParameter($methodBuilder, $method, $parameter);
        }
    }

    private function buildParameter(MethodBuilder $methodBuilder, ReflectionMethod $method, ReflectionParameter $parameter): void
    {
        $parameterBuilder = $methodBuilder->parameter($parameter->name());

        if ($parameter->type()->isDefined()) {
            $type = $parameter->type();
            $imports = $parameter->scope()->nameImports();

            $this->importClassesForMemberType($methodBuilder->end(), $method->class()->name(), $type);

            if ($parameter->isVariadic()) {
                if ($type instanceof ArrayType) {
                    $type = $type->iterableValueType();
                }
            }
            $type = $method->scope()->resolveLocalType($type);
            $parameterBuilder->type($type->short(), $type);
        }

        if ($parameter->isVariadic()) {
            $parameterBuilder->asVariadic();
        }

        if ($parameter->default()->isDefined()) {
            $parameterBuilder->defaultValue($parameter->default()->value());
        }

        if ($parameter->byReference()) {
            $parameterBuilder->byReference(true);
        }
    }

    private function importClassesForMemberType(ClassLikeBuilder $classBuilder, ClassName $classType, Type $type): void
    {
        foreach ($type->allTypes()->classLike() as $types) {
            if ($classType->namespace() == $types->name()->namespace()) {
                return;
            }

            $classBuilder->end()->use($types->name()->full());
        }
    }
}
