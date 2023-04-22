<?php

namespace Phpactor\Extension\Laravel\Providers;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Visibility;

class LaravelQueryBuilderProvider implements ReflectionMemberProvider
{
    private array $virtualBuilders = ['LaravelHasManyVirtualBuilder', 'LaravelBelongsToManyVirtualBuilder'];

    public function __construct(private LaravelContainerInspector $laravelContainer)
    {
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $builderClass): ReflectionMemberCollection
    {
        $list = [];
        if (in_array($builderClass->name()->__toString(), $this->virtualBuilders)) {
            $type = $builderClass->templateMap()->get('TModelClass');
            if ($type instanceof MissingType) {
                return ChainReflectionMemberCollection::fromCollections([]);
            }

            $builderType = match ($builderClass->name()->__toString()) {
                'LaravelHasManyVirtualBuilder' => 'HasMany',
                'LaravelBelongsToManyVirtualBuilder' => ' BelongsToMany',
            };

            return ChainReflectionMemberCollection::fromCollections([
                ReflectionMethodCollection::fromReflectionMethods(
                    $this->getVirtualBuilderMethodsFor($type, $builderType, $locator, $builderClass)
                )
            ]);
        }

        return ChainReflectionMemberCollection::fromCollections([]);
    }
    /**
     * @return array<int,Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod>
     */
    private function getVirtualBuilderMethodsFor(
        ClassType $type,
        string $builderType,
        ServiceLocator $locator,
        ReflectionClassLike $builderClass
    ): array {
        $methods = [];
        if ($modelData = $this->laravelContainer->models()[$type->name()->__toString()] ?? false) {
            $class = $locator->reflector()->reflectClass($type->name());

            $relationBuilder = new GenericClassType($locator->reflector(), $builderClass->name(), [$class->type()]);

            foreach ($modelData['attributes'] as $attributeData) {
                if (!$attributeData['type']) {
                    continue;
                }

                foreach ($attributeData['magicMethods'] ?? [] as $name => $magicMethod) {
                    $methods[] = $method = new VirtualReflectionMethod(
                        $builderClass->position(),
                        $builderClass,
                        $builderClass,
                        $name,
                        new Frame(),
                        new PlainDocblock('Magic method to filter the query by: ' . $name),
                        $builderClass->scope(),
                        Visibility::public(),
                        $relationBuilder,
                        $relationBuilder,
                        ReflectionParameterCollection::empty(),
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
                            position: $method->position(),
                            index: 0,
                        )
                    );
                }
            }
        }

        return $methods;
    }
}
