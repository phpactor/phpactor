<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;
use Phpactor\CodeBuilder\Domain\Prototype\Docblock;

abstract class ClassLikeBuilder extends AbstractBuilder implements Builder
{
    /**
     * @var MethodBuilder[]
     */
    protected array $methods = [];

    protected ?Docblock $docblock = null;

    public function __construct(
        private SourceCodeBuilder $parent,
        protected string $name
    ) {
        $this->docblock = null;
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

    public function docblock(string $docblock): ClassLikeBuilder
    {
        $this->docblock = Docblock::fromString($docblock);

        return $this;
    }

    public function getDocblock(): ?Docblock
    {
        return $this->docblock;
    }

}
