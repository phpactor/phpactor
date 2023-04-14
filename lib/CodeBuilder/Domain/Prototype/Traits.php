<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<TraitPrototype>
 */
class Traits extends Collection
{
    /**
     * @param list<TraitPrototype> $traits
     */
    public static function fromTraits(array $traits): self
    {
        return new static(array_reduce($traits, function ($arr, TraitPrototype $trait) {
            $arr[$trait->name()] = $trait;
            return $arr;
        }, []));
    }

    protected function singularName(): string
    {
        return 'trait';
    }
}
