<?php

namespace Phpactor\Extension\Laravel\Providers;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
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
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;

class LaravelModelPropertiesProvider implements ReflectionMemberProvider
{
    private array $cache = [];

    public function __construct(private LaravelContainerInspector $laravelContainer)
    {
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $className = $class->name()->__toString();

        if (isset($this->cache[$className])) {
            return $this->cache[$className];
        }

        $modelsData = $this->laravelContainer->models();

        if (!isset($modelsData[$className])) {
            return ChainReflectionMemberCollection::fromCollections([]);
        }

        $properties = [];
        $methods = [];

        $modelData = $modelsData[$className];

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
                $locType = $this->laravelContainer->getTypeFromString($attributeData['type'], $locator->reflector(), $attributeData['cast']),
                $locType,
                new Deprecation(false),
            );

            foreach ($attributeData['magicMethods'] ?? [] as $name => $magicMethod) {
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

                $type = $this->laravelContainer->getTypeFromString($attributeData['type'], $locator->reflector(), $attributeData['cast']);

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
                $relType = $this->laravelContainer->getRelationType($relationData['property'], $relationData['type'], $relationData['related'], $locator->reflector()),
                $relType,
                new Deprecation(false),
            );

            $relationBuilder = $this->laravelContainer->getRelationBuilderClassType($class, $relationData, $locator->reflector());

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

        $this->cache[$className] = ChainReflectionMemberCollection::fromCollections([
            ReflectionPropertyCollection::fromReflectionProperties($properties),
            ReflectionMethodCollection::fromReflectionMethods($methods)
        ]);

        return $this->cache[$className];
    }
}
