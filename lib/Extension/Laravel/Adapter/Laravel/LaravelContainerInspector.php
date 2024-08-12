<?php

namespace Phpactor\Extension\Laravel\Adapter\Laravel;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Reflector;
use Symfony\Component\Process\Process;

/**
 * This calls external tooling that is capable of extracting the required information from a Laravel codebase.
 *
 * At some point we should listen for certain file changes to invalidate the in-memory cache.
 *
 * @todo: Add the stub on launch.
 * @todo: SourceNotFound exception with stubs when not there.
 */
class LaravelContainerInspector
{
    public array $relationTypeCache = [];

    private ?array $services = null;

    private ?array $views = null;

    private ?array $config = null;

    private ?array $routes = null;

    private ?array $models = null;

    private ?array $viewsData = null;

    private array $methodAndPropertiesCache = [];

    public function __construct(private string $executablePath, private string $projectRoot)
    {
    }

    public function service(string $id): ?ClassType
    {
        foreach ($this->services() as $short => $service) {
            if ($short === $id || $service === $id) {
                return TypeFactory::fromString('\\' . $service);
            }
        }
        return null;
    }

    public function services(): array
    {
        if ($this->services === null) {
            $this->services = $this->getGetterOutput('container');
        }

        return $this->services;
    }

    public function views(): array
    {
        if ($this->views === null) {
            $this->views = $this->getGetterOutput('views');
        }

        return $this->views;
    }

    public function routes(): array
    {
        if ($this->routes === null) {
            $this->routes = $this->getGetterOutput('routes');
        }

        return $this->routes;
    }

    public function config(): array
    {
        if ($this->config === null) {
            $this->config = $this->getGetterOutput('config');
        }

        return $this->config;
    }

    public function models(): array
    {
        if ($this->models === null) {
            $this->models = $this->getGetterOutput('models');
        }

        return $this->models;
    }

    public function viewsData(): array
    {
        if ($this->viewsData === null) {
            $this->viewsData = $this->getGetterOutput('snippets');
        }

        return $this->viewsData;
    }

    public function getMethodsAndPropertiesForClass(
        ReflectionClassLike $parentClass,
        ReflectionClassLike $targetClass,
        array $modelData,
        Reflector $reflector
    ): ChainReflectionMemberCollection {
        $className = $parentClass->name()->__toString();
        $targetClassName = $targetClass->name()->__toString();

        if (isset($this->methodAndPropertiesCache[$className . '-' . $targetClassName])) {
            return $this->methodAndPropertiesCache[$className . '-' . $targetClassName];
        }

        $properties = [];
        $methods = [];

        $relationBuilder = $this->getRelationBuilderClassType('Builder', $targetClass->name()->__toString(), $reflector);
        $reflectedRelationBuilder = $relationBuilder->reflectionOrNull();


        if (str_starts_with($className, 'Laravel') && str_ends_with($className, 'VirtualBuilder')) {
            $type = $parentClass->templateMap()->get('TModelClass');
            $builderForWheres = new GenericClassType($reflector, $parentClass->name(), [$type]);
        } else {
            $builderForWheres = $relationBuilder;
        }

        // Base virtual/Builder methods.
        foreach ($this->getMethodsToGenerate($targetClass->type(), $builderForWheres, $reflector) as $methodName => $methodData) {
            $methods[] = $method = new VirtualReflectionMethod(
                $parentClass->position(),
                $parentClass,
                $parentClass,
                $methodName,
                new Frame(),
                new PlainDocblock($methodData['description']),
                $parentClass->scope(),
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

            $properties[] = new VirtualReflectionProperty(
                $parentClass->position(),
                $parentClass,
                $parentClass,
                $attributeData['name'],
                new Frame(),
                new PlainDocblock(''),
                $parentClass->scope(),
                Visibility::public(),
                $locType = $this->getTypeFromString($attributeData['type'], $reflector),
                $locType,
                new Deprecation(false),
            );

            foreach ($attributeData['magicMethods'] ?? [] as $name => $magicMethod) {
                $methods[] = $method = new VirtualReflectionMethod(
                    $parentClass->position(),
                    $parentClass,
                    $parentClass,
                    $name,
                    new Frame(),
                    new PlainDocblock(''),
                    $parentClass->scope(),
                    Visibility::public(),
                    $relationBuilder,
                    $relationBuilder,
                    ReflectionParameterCollection::empty(), // @todo
                    NodeText::fromString(''),
                    false,
                    true,
                    new Deprecation(false),
                );

                $type = $this->getTypeFromString($magicMethod['type'], $reflector);

                $method->parameters()->add(
                    new VirtualReflectionParameter(
                        name: 'argument',
                        functionLike: $method,
                        inferredType: $type,
                        type: $type,
                        default: DefaultValue::undefined(),
                        byReference: false,
                        scope: $method->scope(),
                        position: $parentClass->position(),
                        index: 0,
                    )
                );
            }
        }

        foreach ($modelData['scopes'] as $scope) {
            // Also replace the method.
            $methods[] = $method = new VirtualReflectionMethod(
                $reflectedRelationBuilder->position(),
                $reflectedRelationBuilder,
                $reflectedRelationBuilder,
                $scope,
                new Frame(),
                new PlainDocblock(''),
                $reflectedRelationBuilder->scope(),
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

        foreach ($modelData['relations'] as $relationData) {
            $properties[] = new VirtualReflectionProperty(
                $parentClass->position(),
                $parentClass,
                $parentClass,
                $relationData['property'],
                new Frame(),
                new PlainDocblock(''),
                $parentClass->scope(),
                Visibility::public(),
                $relType = $this->getRelationType($relationData['isMany'], $relationData['related'], $reflector),
                $relType,
                new Deprecation(false),
            );

            if ($relationBuilder = $this->getRelationBuilderClassType($relationData['type'], $relationData['related'], $reflector)) {
                $reflected = $relationBuilder->reflectionOrNull();
                // Also replace the method.
                $methods[] = $method = new VirtualReflectionMethod(
                    $reflected->position(),
                    $reflected,
                    $reflected,
                    $relationData['property'],
                    new Frame(),
                    new PlainDocblock(''),
                    $reflected->scope(),
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

        $this->methodAndPropertiesCache[$className . '-' . $targetClassName] = ChainReflectionMemberCollection::fromCollections([
            ReflectionPropertyCollection::fromReflectionProperties($properties),
            ReflectionMethodCollection::fromReflectionMethods($methods)
        ]);

        return $this->methodAndPropertiesCache[$className . '-' . $targetClassName];
    }

    public function getRelationBuilderClassType(string $type, string $targetType, Reflector $reflector): ?GenericClassType
    {
        $class = null;

        try {
            if ($type === 'Illuminate\Database\Eloquent\Builder') {
                $class = $reflector->reflectClass('LaravelBuilder');
            }
            if ($type === 'Illuminate\Database\Eloquent\Relations\HasMany') {
                $class = $reflector->reflectClass('LaravelHasManyVirtualBuilder');
            }
            if ($type === 'Staudenmeir\EloquentHasManyDeep\HasManyDeep') {
                $class = $reflector->reflectClass('LaravelHasManyVirtualBuilder');
            }
            if ($type === 'Illuminate\Database\Eloquent\Relations\BelongsTo') {
                $class = $reflector->reflectClass('LaravelBelongsToVirtualBuilder');
            }
            if ($type === 'Illuminate\Database\Eloquent\Relations\BelongsToMany') {
                $class = $reflector->reflectClass('LaravelBelongsToManyVirtualBuilder');
            }
            if ($type === 'Builder') {
                $class = $reflector->reflectClass('LaravelQueryVirtualBuilder');
            }

            if ($class) {
                $relationClass = $reflector->reflectClass(ClassName::fromString($targetType));

                return new GenericClassType($reflector, $class->name(), [$relationClass->type()]);
            }
        } catch (SourceNotFound) {
        }

        return null;
    }

    public function getRelationType(
        bool $isMany,
        string $related,
        Reflector $reflector
    ): GenericClassType|ReflectedClassType {
        $cacheKey = ($isMany ? '1' : '0') . '|' . $related;

        if (!isset($this->relationTypeCache[$cacheKey])) {
            if ($isMany) {
                $this->relationTypeCache[$cacheKey] = new GenericClassType(
                    $reflector,
                    ClassName::fromString('\\Illuminate\\Database\\Eloquent\\Collection'),
                    [
                        new IntType(),
                        new ReflectedClassType($reflector, ClassName::fromString($related)),
                    ]
                );
            } else {
                $this->relationTypeCache[$cacheKey] = new ReflectedClassType(
                    $reflector,
                    ClassName::fromString($related)
                );
            }
        }

        return $this->relationTypeCache[$cacheKey];
    }

    public function getTypeFromString(string $phpType, Reflector $reflector): Type
    {
        if (str_contains($phpType, '\\')) {
            return $reflector->reflectClassLike(ClassName::fromString($phpType))->type();
        }

        return match ($phpType) {
            'string' => new StringType(),
            'int' => new IntType(),
            'bool' => new BooleanType(),
            'DateTime' => $reflector->reflectClass(ClassName::fromString('\\Carbon\\Carbon'))->type(),
            default => new StringType(),
        };
    }

    /**
     * @todo: Ideally this also runs after a migration, but I am not sure if we can detect that.
     * The laravel-dev-tools package could take care of that.
     */
    public function modelChanged(ReflectionClassLike $class): void
    {
        $this->models = null;

        foreach ($this->relationTypeCache as $key => $val) {
            if (str_contains($key, $class->name()->__toString())) {
                unset($this->relationTypeCache[$key]);
            }
        }
        foreach ($this->methodAndPropertiesCache as $key => $val) {
            if (str_contains($key, $class->name()->__toString())) {
                unset($this->methodAndPropertiesCache[$key]);
            }
        }
    }

    public function livewireComponentChanged(ReflectionClassLike $class): void
    {
        $this->viewsData = null;
    }

    public function bladeComponentChanged(ReflectionClassLike $class): void
    {
        $this->viewsData = null;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function getMethodsToGenerate(Type $targetType, GenericClassType $builder, Reflector $reflector): array
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
            'make' => [
                'description' => 'Make a new model',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(new StringType(), new MixedType()),
                        'required' => true,
                    ],
                ],
                'returns' => $targetType,
            ],
            'update' => [
                'description' => 'Updates the models in the result',
                'arguments' => [
                    'attributes' => [
                        'type' => new ArrayType(new StringType(), new MixedType()),
                        'required' => true,
                    ],
                ],
                'returns' => new BooleanType(),
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
            'paginate' => [
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
            'each' => [
                'description' => 'Iterate',
                'arguments' => [
                    'closure' => [
                        'type' => new ClosureType($reflector, [$targetType]),
                    ],
                ],
                'returns' => new BooleanType(),
            ],
        ];

        $simpleCollectionMethods = [
            'pluck',
        ];

        foreach ($simpleCollectionMethods as $simpleCollectionMethod) {
            $methodListToGenerate[$simpleCollectionMethod] = [
                'description' => $simpleCollectionMethod,
                'arguments' => [],
                'returns' => $reflector->reflectClass('\Illuminate\Support\Collection')->type(),
            ];
        }

        $intMethods = [
            'count',
            'max',
            'min',
        ];

        foreach ($intMethods as $intMethod) {
            $methodListToGenerate[$intMethod] = [
                'description' => $intMethod,
                'arguments' => [],
                'returns' => new IntType(),
            ];
        }

        $boolMethods = [
            'exists',
            'doesntExist',
        ];

        foreach ($boolMethods as $boolMethod) {
            $methodListToGenerate[$boolMethod] = [
                'description' => $boolMethod,
                'arguments' => [],
                'returns' => new BooleanType(),
            ];
        }

        $whereMethods = [
            'where',
            'whereHas',
            'whereNull',
            'whereNotNull',
            'whereNull',
            'whereBetween',
            'select',
            'reorder',
            'whereIn',
            'orWhereIn',
            'inRandomOrder',
            'orderBy',
            'limit',
            'whereHas',
            'whereRaw',
            'whereDoesntHave',
            'withCount',
            'with',
            'latest'
        ];

        foreach ($whereMethods as $whereMethod) {
            $methodListToGenerate[$whereMethod] = [
                'description' => $whereMethod,
                'arguments' => [],
                'returns' => $builder,
            ];
        }

        return $methodListToGenerate;
    }

    /**
     * @return mixed|array
     */
    private function getGetterOutput(string $getter): array
    {
        $process = new Process([$this->executablePath, $getter, $this->projectRoot]);
        $process->run();

        if ($process->isSuccessful()) {
            return json_decode(trim($process->getOutput()), true);
        }

        return [];
    }
}
