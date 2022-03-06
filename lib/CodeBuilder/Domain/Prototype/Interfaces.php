<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Interfaces extends Collection
{
    public static function fromInterfaces(array $interfaces)
    {
        return new static($interfaces);
    }

    protected function singularName(): string
    {
        return 'interface';
    }
}
