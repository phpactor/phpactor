<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Constant>
 */
class Constants extends Collection
{
    public static function fromConstants(array $constants)
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
