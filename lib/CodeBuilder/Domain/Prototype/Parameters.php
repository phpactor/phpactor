<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Parameter first()
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Parameter get(string $name)
 */
class Parameters extends Collection
{
    public static function fromParameters(array $parameters)
    {
        return new self($parameters);
    }

    protected function singularName(): string
    {
        return 'parameter';
    }
}
