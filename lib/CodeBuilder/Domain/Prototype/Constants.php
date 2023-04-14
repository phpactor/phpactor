<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Constant>
 */
class Constants extends Collection
{
    /**
    * @param list<Constant> $constants
    */
    public static function fromConstants(array $constants): self
    {
        return new static(array_reduce($constants, function ($acc, $constant) {
            $acc[$constant->name()] = $constant;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'constant';
    }
}
