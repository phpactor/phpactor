<?php

namespace Phpactor\Extension\Laravel\Providers;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Reflector;

class LaravelModelPropertiesProvider implements ReflectionMemberProvider
{
    public function __construct(private LaravelContainerInspector $laravelContainer)
    {
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $properties = [];
        $methods = [];

        $modelsData = $this->laravelContainer->models();

        if (isset($modelsData[$class->name()->__toString()])) {
            $modelData = $modelsData[$class->name()->__toString()];

            foreach ($modelData['attributes'] as $attributeData) {
                if (!$attributeData['type']) {
                    continue;
                }

                $properties[] = new VirtualReflectionProperty(
                    $class->position(),
                    $class,
                    $class,
                    $attributeData['name'],
                    new Frame(),
                    $class->docblock(),
                    $class->scope(),
                    Visibility::public(),
                    $locType = $this->getTypeFromString($attributeData['type'], $locator->reflector(), $attributeData['cast']),
                    $locType,
                    new Deprecation(false),
                );

                foreach ($attributeData['magicMethods'] as $name => $magicMethod) {
                    $methods[] = $method = new VirtualReflectionMethod(
                        $class->position(),
                        $class,
                        $class,
                        $name,
                        new Frame(),
                        $class->docblock(),
                        $class->scope(),
                        Visibility::public(),
                        new StaticType(),
                        new StaticType(),
                        ReflectionParameterCollection::empty(), // @todo
                        NodeText::fromString(''),
                        false,
                        true,
                        new Deprecation(false),
                    );

                    $type = $this->getTypeFromString($attributeData['type'], $locator->reflector(), $attributeData['cast']);

                    $method->parameters()->add(
                        new VirtualReflectionParameter(
                            name: 'argument',
                            functionLike: $method,
                            inferredType: $type,
                            type: $type,
                            default: DefaultValue::undefined(),
                            byReference: false,
                            scope: $method->scope(),
                            position: $class->position(),
                            index: 0,
                        )
                    );
                }
            }

            foreach ($modelData['relations'] as $relationData) {
                $properties[] = new VirtualReflectionProperty(
                    $class->position(),
                    $class,
                    $class,
                    $relationData['property'],
                    new Frame(),
                    $class->docblock(),
                    $class->scope(),
                    Visibility::public(),
                    $relType = $this->getRelationType($relationData['property'], $relationData['type'], $relationData['related'], $locator->reflector()),
                    $relType,
                    new Deprecation(false),
                );

                $relationBuilder = $this->getRelationBuilderClassType($class, $relationData, $locator->reflector());

                // Also replace the method.
                $methods[] = $method = new VirtualReflectionMethod(
                    $class->position(),
                    $class,
                    $class,
                    $relationData['property'],
                    new Frame(),
                    $class->docblock(),
                    $class->scope(),
                    Visibility::public(),
                    $relationBuilder,
                    $relationBuilder,
                    ReflectionParameterCollection::empty(),
                    NodeText::fromString(''),
                    false,
                    true,
                    new Deprecation(false),
                );
            }
        }

        return ChainReflectionMemberCollection::fromCollections([
            ReflectionPropertyCollection::fromReflectionProperties($properties ?? []),
            ReflectionMethodCollection::fromReflectionMethods($methods ?? [])
        ]);
    }

    private function getRelationBuilderClassType(ReflectionClassLike $parentClass, array $relationData, Reflector $reflector): Type
    {
        $className = ClassName::fromString(ucfirst($relationData['property'] . 'VirtualBuilder'));

        $builderClass = new ReflectedClassType($reflector, $className);

        $relationClass = new ReflectedClassType($reflector, ClassName::fromString($relationData['type']));
        $relationClassReflected = $reflector->reflectClass(ClassName::fromString($relationData['type']));

        // Now that we have the class, we can add the methods.
        $targetClass = $reflector->reflectClass(ClassName::fromString($relationData['related']));

        $methods = [];

        $methods[] = $method = new VirtualReflectionMethod(
            $targetClass->position(),
            $targetClass,
            $targetClass,
            'create',
            new Frame(),
            new PlainDocblock(''),
            $targetClass->scope(),
            Visibility::public(),
            $targetClass->type(),
            $targetClass->type(),
            ReflectionParameterCollection::empty(),
            NodeText::fromString(''),
            false,
            false,
            new Deprecation(false),
        );

        $builderClass = $builderClass
            ->mergeMembers(ReflectionMethodCollection::fromReflectionMethods($methods))
            ->mergeMembers($relationClassReflected->members());

        return $builderClass;
        /* $method->parameters()->add( */
        /*     new VirtualReflectionParameter( */
        /*         name: 'argument', */
        /*         functionLike: $method, */
        /*         inferredType: $targetClass, */
        /*         type: $targetClass, */
        /*         default: DefaultValue::undefined(), */
        /*         byReference: false, */
        /*         scope: $method->scope(), */
        /*         position: $parentClass->position(), */ /*         index: 0, */
        /*     ) */
        /* ); */
        /*  */
    }

    private function getRelationType(string $name, string $type, string $related, Reflector $reflector): GenericClassType|ReflectedClassType
    {
        // @todo: This is currently a dumb approach.
        $isMany = str_contains($type, 'Many');

        if ($isMany) {
            return new GenericClassType(
                $reflector,
                ClassName::fromString('\\Illuminate\\Database\\Eloquent\\Collection'),
                [
                    new IntType(),
                    new ReflectedClassType($reflector, ClassName::fromString($related)),
                ]
            );
        }

        return new ReflectedClassType($reflector, ClassName::fromString($related));
    }

    private function getTypeFromString(string $phpType, Reflector $reflector, ?string $cast = null): Type
    {

        $type = null;
        if ($cast) {
            $type = match ($cast) {
                'datetime' => new ReflectedClassType($reflector, ClassName::fromString('\\Carbon\\Carbon')),
                default => new ReflectedClassType($reflector, ClassName::fromString($cast)),
            };
        }

        if ($type) {
            return $type;
        }

        return match ($phpType) {
            'string' => new StringType(),
            'int' => new IntType(),
            'bool' => new BooleanType(),
            'DateTime' => new ReflectedClassType($reflector, ClassName::fromString('\\Carbon\\Carbon')),
            default => new StringType(),
        };
    }
}
