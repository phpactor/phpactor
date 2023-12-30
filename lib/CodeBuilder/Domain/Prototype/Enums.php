<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<EnumPrototype>
 */
class Enums extends Collection
{
    /**
     * @param list<EnumPrototype> $enums
     */
    public static function fromEnums(array $enums): self
    {
        return new static(array_reduce($enums, function ($arr, EnumPrototype $enum) {
            $arr[$enum->name()] = $enum;
            return $arr;
        }, []));
    }

    protected function singularName(): string
    {
        return 'trait';
    }
}
