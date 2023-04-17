<?php

namespace Phpactor\Extension\Laravel\Providers;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
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
        $modelsData = $this->laravelContainer->models();

        if (isset($modelsData[$class->name()->__toString()])) {
            $modelData = $modelsData[$class->name()->__toString()];

            foreach ($modelData['attributes'] as $attributeData) {
                $properties[] = new VirtualReflectionProperty(
                    $class->position(),
                    $class,
                    $class,
                    $attributeData['name'],
                    new Frame(),
                    $class->docblock(),
                    $class->scope(),
                    Visibility::public(),
                    $this->getTypeFromString($attributeData['type'], $locator->reflector(), $attributeData['cast']),
                    $this->getTypeFromString($attributeData['type'], $locator->reflector(), $attributeData['cast']),
                    new Deprecation(false),
                );
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
                    $this->getRelationType($relationData['property'], $relationData['type'], $relationData['related'], $locator->reflector()),
                    $this->getRelationType($relationData['property'], $relationData['type'], $relationData['related'], $locator->reflector()),
                    new Deprecation(false),
                );
            }
        }

        return ChainReflectionMemberCollection::fromCollections([
            ReflectionPropertyCollection::fromReflectionProperties($properties ?? [])
        ]);
    }

    private function getRelationType(string $name, string $type, string $related, Reflector $reflector): Type
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
