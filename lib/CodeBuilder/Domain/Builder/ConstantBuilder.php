<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Constant;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;
use Phpactor\CodeBuilder\Domain\Prototype\Value;

class ConstantBuilder extends AbstractBuilder implements NamedBuilder
{
    /**
     * @var mixed
     */
    protected $value;

    public function __construct(private ClassLikeBuilder $parent, protected string $name, mixed $value)
    {
        $this->value = Value::fromValue($value);
    }

    public static function childNames(): array
    {
        return [];
    }

    public function build(): Constant
    {
        return new Constant(
            $this->name,
            $this->value,
            UpdatePolicy::fromModifiedState($this->isModified())
        );
    }

    public function end(): ClassLikeBuilder
    {
        return $this->parent;
    }

    public function builderName(): string
    {
        return $this->name;
    }
}
