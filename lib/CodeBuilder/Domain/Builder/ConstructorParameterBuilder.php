<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;

class ConstructorParameterBuilder extends ParameterBuilder
{
    private ?Visibility $visibility = null;

    public function __construct(private ConstructorBuilder $parent, protected string $name)
    {
        parent::__construct($parent, $name);
    }

    public function visibility(?Visibility $visibility): ConstructorParameterBuilder
    {
        $this->visibility = $visibility;

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
            $this->visibility
        );
    }
}
