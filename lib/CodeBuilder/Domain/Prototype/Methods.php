<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Method>
 */
class Methods extends Collection
{
    /**
     * @param Method[] $methods
     */
    public static function fromMethods(array $methods): self
    {
        return new self(array_reduce($methods, function ($acc, $method) {
            $acc[$method->name()] = $method;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'method';
    }
}
