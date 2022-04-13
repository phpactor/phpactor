<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;

class ParameterBuilder extends AbstractBuilder
{
    protected string $name;

    protected ?Type $type = null;

    protected ?DefaultValue $defaultValue = null;

    protected bool $byReference = false;

    private MethodBuilder $parent;

    private bool $variadic = false;

    public function __construct(MethodBuilder $parent, string $name)
    {
        $this->parent = $parent;
        $this->name = $name;
    }

    /**
     * @return array{}
     */
    public static function childNames(): array
    {
        return [];
    }

    public function type(string $type): ParameterBuilder
    {
        $this->type = Type::fromString($type);

        return $this;
    }

    public function defaultValue($value): ParameterBuilder
    {
        $this->defaultValue = DefaultValue::fromValue($value);

        return $this;
    }

    public function build(): Parameter
    {
        return new Parameter(
            $this->name,
            $this->type,
            $this->defaultValue,
            $this->byReference,
            UpdatePolicy::fromModifiedState($this->isModified()),
            $this->variadic,
        );
    }

    public function end(): MethodBuilder
    {
        return $this->parent;
    }

    public function byReference(bool $bool): self
    {
        $this->byReference = $bool;

        return $this;
    }

    public function asVariadic(): self
    {
        $this->variadic = true;

        return $this;
    }
}
