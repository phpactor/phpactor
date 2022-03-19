<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Builder\ClassLikeBuilder;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\TypeUtil;

class WorseBuilderFactory implements BuilderFactory
{
    /**
     * @var Reflector|ClassName
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function fromSource($source): SourceCodeBuilder
    {
        if (!$source instanceof TextDocument) {
            $source = TextDocumentBuilder::create($source)
                ->language('php')
                ->build();
        }

        $classes = $this->reflector->reflectClassesIn($source);
        $builder = SourceCodeBuilder::create();

        foreach ($classes as $class) {
            if ($class->isClass()) {
                $this->build('class', $builder, $class);
                continue;
            }

            if ($class->isInterface()) {
                $this->build('interface', $builder, $class);
                continue;
            }

            if ($class->isTrait()) {
                $this->build('trait', $builder, $class);
                continue;
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
        $propertyBuilder = $classBuilder->property($property->name());
        $propertyBuilder->visibility((string) $property->visibility());

        $type = $property->inferredTypes()->best();
        if (TypeUtil::isDefined($type)) {
            $this->resolveClassMemberType($classBuilder, $property->class()->name(), $type);
            $propertyBuilder->type(TypeUtil::short($type));
        }
    }

    private function buildMethod(ClassLikeBuilder $classBuilder, ReflectionMethod $method): void
    {
        $methodBuilder = $classBuilder->method($method->name());
        $methodBuilder->visibility((string) $method->visibility());

        if (TypeUtil::isDefined($method->returnType())) {
            $type = $method->returnType();
            $this->resolveClassMemberType($classBuilder, $method->class()->name(), $type);
            $typeName = TypeUtil::short($type);
            if (TypeUtil::isNullable($type)) {
                $typeName = '?' . $typeName;
            }
            $methodBuilder->returnType($typeName);
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

        if (TypeUtil::isDefined($parameter->type())) {
            $type = $parameter->type();
            $imports = $parameter->scope()->nameImports();

            $this->resolveClassMemberType($methodBuilder->end(), $method->class()->name(), $type);

            $typeName = $this->resolveTypeNameFromNameImports($type, $imports);

            if (TypeUtil::isNullable($type)) {
                $typeName = '?' . $typeName;
            }

            $parameterBuilder->type($typeName);
        }

        if ($parameter->default()->isDefined()) {
            $parameterBuilder->defaultValue($parameter->default()->value());
        }

        if ($parameter->byReference()) {
            $parameterBuilder->byReference(true);
        }
    }

    private function resolveClassMemberType(ClassLikeBuilder $classBuilder, ClassName $classType, Type $type): void
    {
        if ($type instanceof ClassType && $classType->namespace() != $type->name()->namespace()) {
            $classBuilder->end()->use($type->name()->full());
        }
    }

    private function resolveTypeNameFromNameImports(Type $type, NameImports $imports)
    {
        $typeName = TypeUtil::short($type);

        foreach ($imports as $alias => $import) {
            if ($typeName == $import->head()) {
                $typeName = $alias;
            }
        }

        return $typeName;
    }
}
