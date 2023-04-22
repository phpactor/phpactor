<?php

namespace Phpactor\Extension\Laravel\Adapter\Laravel;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Reflector;
use Symfony\Component\Process\Process;

/**
 * This calls external tooling that is capable of extracting the required information from a Laravel codebase.
 *
 * At some point we should listen for certain file changes to invalidate the in-memory cache.
 */
class LaravelContainerInspector
{
    private ?array $services = null;

    private ?array $views = null;

    private ?array $routes = null;

    private ?array $models = null;

    private ?array $snippets = null;

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

    public function models(): array
    {
        if ($this->models === null) {
            $this->models = $this->getGetterOutput('models');
        }

        return $this->models;
    }

    public function snippets(): array
    {
        if ($this->snippets === null) {
            $this->snippets = $this->getGetterOutput('snippets');
        }

        return $this->snippets;
    }

    public function getRelationBuilderClassType(ReflectionClassLike $parentClass, array $relationData, Reflector $reflector): Type
    {
        if ($relationData['type'] === 'Illuminate\Database\Eloquent\Relations\HasMany') {
            $class = $reflector->reflectClass('LaravelHasManyVirtualBuilder');
            $relationClass = new ReflectedClassType($reflector, ClassName::fromString($relationData['related']));

            return new GenericClassType($reflector, $class->name(), [$relationClass]);
        }

        if ($relationData['type'] === 'Illuminate\Database\Eloquent\Relations\BelongsToMany') {
            $class = $reflector->reflectClass('LaravelBelongsToManyVirtualBuilder');
            $relationClass = new ReflectedClassType($reflector, ClassName::fromString($relationData['related']));

            return new GenericClassType($reflector, $class->name(), [$relationClass]);
        }

        return new MissingType();
    }

    public function getRelationType(
        string $name,
        string $type,
        string $related,
        Reflector $reflector
    ): GenericClassType|ReflectedClassType {
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

    public function getTypeFromString(string $phpType, Reflector $reflector, ?string $cast = null): Type
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
