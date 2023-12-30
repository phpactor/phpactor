<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Property>
 */
class Properties extends Collection
{
    /**
     * @param array<Property> $properties
     */
    public static function fromProperties(array $properties): self
    {
        return new static(array_reduce($properties, function ($acc, Property $property) {
            $acc[$property->name()] = $property;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'property';
    }
}
