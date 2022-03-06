<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class ImplementsInterfaces extends Collection
{
    public function __toString()
    {
        return implode(', ', array_reduce($this->items, function ($acc, $interfaceName) {
            $acc[] = $interfaceName->__toString();
            return $acc;
        }));
    }
    public static function fromTypes(array $types)
    {
        return new static(array_reduce($types, function ($acc, $type) {
            $acc[(string) $type] = $type;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'implement interface';
    }
}
