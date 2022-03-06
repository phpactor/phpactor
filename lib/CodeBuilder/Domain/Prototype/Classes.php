<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @method \Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype first()
 * @method \Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype get()
 */
class Classes extends Collection
{
    public static function fromClasses(array $classes)
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
