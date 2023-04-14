<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Type>
 */
class ExtendsInterfaces extends Collection
{
    /**
     * @param list<Type> $types
     */
    public static function fromTypes(array $types): self
    {
        return new self($types);
    }

    protected function singularName(): string
    {
        return 'extend interface';
    }
}
