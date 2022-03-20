<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionClass first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionClass last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionClass get(string $name)
 */
interface ReflectionClassCollection extends ReflectionCollection
{
    public function concrete();
}
