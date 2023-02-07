<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;
use Phpactor\CodeBuilder\Domain\Builder\ConstructorBuilder;

abstract class ClassLikeBuilder extends AbstractBuilder implements Builder
{
    /**
     * @var MethodBuilder[]
     */
    protected array $methods = [];

    public function __construct(private SourceCodeBuilder $parent, protected string $name)
    {
    }

    public static function childNames(): array
    {
        return [
            'methods',
        ];
    }

    public function add(Builder $builder): void
    {
        if ($builder instanceof MethodBuilder) {
            $this->methods[$builder->builderName()] = $builder;
            return;
        }

        throw new InvalidBuilderException($this, $builder);
    }

    public function method(string $name): MethodBuilder|ConstructorBuilder
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }

        $builder = match($name) {
            '__construct' => new ConstructorBuilder($this, $name),
            default => new MethodBuilder($this, $name),
        };
        $this->methods[$name] = $builder;

        return $builder;
    }

    public function end(): SourceCodeBuilder
    {
        return $this->parent;
    }
}
