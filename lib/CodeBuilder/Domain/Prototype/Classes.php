<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<ClassPrototype>
 */
class Classes extends Collection
{
    public static function fromClasses(array $classes): self
    {
        return new static(array_reduce($classes, function ($acc, $class) {
            $acc[$class->name()] = $class;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'class';
    }
}
