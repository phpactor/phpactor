<?php

namespace Phpactor\WorseReflection\Core\Reflection\OldCollection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionClass first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionClass last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionClass get(string $name)
 * @extends ReflectionCollection<ReflectionClass>
 */
interface ReflectionClassCollection extends ReflectionCollection
{
    public function concrete(): self;
}
