<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;

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

    public function method(string $name): MethodBuilder
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }

        $this->methods[$name] = $builder = new MethodBuilder($this, $name);

        return $builder;
    }

    public function end(): SourceCodeBuilder
    {
        return $this->parent;
    }
}
