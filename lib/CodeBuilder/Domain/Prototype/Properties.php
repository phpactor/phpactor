<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Property first()
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Property get()
 */
class Properties extends Collection
{
    public static function fromProperties(array $properties)
    {
        return new static(array_reduce($properties, function ($acc, $property) {
            $acc[$property->name()] = $property;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'property';
    }
}
