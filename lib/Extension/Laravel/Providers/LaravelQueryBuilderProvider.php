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
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Reflector;

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
                'LaravelBelongsToManyVirtualBuilder' => 'BelongsToMany',
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

            $methodsToGenerate = $this->getMethodsToGenerate($class->type(), $relationBuilder, $locator->reflector());

            foreach ($methodsToGenerate as $methodName => $methodData) {
                $methods[] = $method = new VirtualReflectionMethod(
                    $builderClass->position(),
                    $builderClass,
                    $builderClass,
                    $methodName,
                    new Frame(),
                    new PlainDocblock($methodData['description']),
                    $builderClass->scope(),
                    Visibility::public(),
                    $methodData['returns'],
                    $methodData['returns'],
                    ReflectionParameterCollection::empty(),
                    NodeText::fromString(''),
                    false,
                    true,
                    new Deprecation(false),
                );

                $index = 0;
                foreach ($methodData['arguments'] as $argumentName => $argumentData) {
                    // @todo : Check if needed.
                    $required = $argumentData['required'] ?? false;
                    $method->parameters()->add(
                        new VirtualReflectionParameter(
                            name: $argumentName,
                            functionLike: $method,
                            inferredType: $argumentData['type'],
                            type: $argumentData['type'],
                            default: DefaultValue::undefined(),
                            byReference: false,
                            scope: $method->scope(),
                            position: $method->position(),
                            index: $index,
                        )
                    );
                    $index++;
                }
            }

            foreach ($modelData['attributes'] as $attributeData) {
                if (!$attributeData['type']) {
                    continue;
                }

                foreach ($attributeData['magicMethods'] ?? [] as $methodName => $magicMethod) {
                    $methods[] = $method = new VirtualReflectionMethod(
                        $builderClass->position(),
                        $builderClass,
                        $builderClass,
                        $methodName,
                        new Frame(),
                        new PlainDocblock('Magic method to filter the query by: ' . $methodName),
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


    private function getMethodsToGenerate(Type $targetType, Type $builder, Reflector $reflector): array
    {
        $collectionType = new GenericClassType(
            $reflector,
            ClassName::fromString('Illuminate\Database\Eloquent\Collection'),
            [$targetType]
        );


        // To check if needed.
        // - getModel
        // - getModels
        // - newModelInstance
        // - sole

        $methodListToGenerate = [
            'create' => [
                'description' => 'Creates a new model',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(new StringType(), new MixedType()),
                        'required' => true,
                    ],
                ],
                'returns' => $targetType,
            ],
            'find' => [
                'description' => 'Find a model',
                'arguments' => [
                    'primaryKey' => [
                        'type' => new MixedType(),
                        'required' => true,
                    ],
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => UnionType::fromTypes($targetType, new NullType())
            ],
            'findOrFail' => [
                'description' => 'Find a model or throws an exception',
                'arguments' => [
                    'primaryKey' => [
                        'type' => new MixedType(),
                        'required' => true,
                    ],
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => UnionType::fromTypes($targetType, new NullType())
            ],
            'findOrNew' => [
                'description' => 'Find a model or throws an exception',
                'arguments' => [
                    'primaryKey' => [
                        'type' => new MixedType(),
                        'required' => true,
                    ],
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $targetType
            ],
            'first' => [
                'description' => 'The first query result',
                'arguments' => [
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => UnionType::fromTypes($targetType, new NullType())
            ],
            'firstOrCreate' => [
                'description' => 'The first query result or create',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                    'values' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $targetType
            ],
            'firstNew' => [
                'description' => 'The first query result or a new entry',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                    'values' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $targetType
            ],
            'forceCreate' => [
                'description' => 'Force create an entry',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $targetType
            ],
            'firstOrFail' => [
                'description' => 'The first result or an exception',
                'arguments' => [
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $targetType
            ],
            'updateOrCreate' => [
                'description' => 'Update or create a model',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                    'values' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $targetType
            ],
            'get' => [
                'description' => 'Get the results',
                'arguments' => [
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $collectionType,
            ],
            'findMany' => [
                'description' => 'Find many model',
                'arguments' => [
                    'primaryKey' => [
                        'type' => new ArrayType(valueType: new MixedType()),
                        'required' => true,
                    ],
                    'columns' => [
                        'type' => new ArrayType(valueType: new StringType()),
                        'default' => new ArrayType(),
                    ],
                ],
                'returns' => $collectionType,
            ],
        ];

        return $methodListToGenerate;
    }
}
