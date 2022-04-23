<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Type>
 */
class ExtendsInterfaces extends Collection
{
    public static function fromTypes(array $types)
    {
        return new self($types);
    }

    protected function singularName(): string
    {
        return 'extend interface';
    }
}
