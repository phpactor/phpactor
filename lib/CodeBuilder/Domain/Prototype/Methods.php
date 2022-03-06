<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Method first()
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Method get()
 */
class Methods extends Collection
{
    public static function fromMethods(array $methods)
    {
        return new static(array_reduce($methods, function ($acc, $method) {
            $acc[$method->name()] = $method;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'method';
    }
}
