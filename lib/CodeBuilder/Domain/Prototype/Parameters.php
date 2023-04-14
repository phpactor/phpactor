<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Parameter>
 */
class Parameters extends Collection
{
    /**
     * @param array<Parameter> $parameters
     */
    public static function fromParameters(array $parameters): self
    {
        return new self($parameters);
    }

    protected function singularName(): string
    {
        return 'parameter';
    }
}
