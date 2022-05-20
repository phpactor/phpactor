<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @method ReflectionMethod first()
 * @method ReflectionMethod last()
 * @method ReflectionMethod get(string $name)
 * @method ReflectionMethodCollection merge(ReflectionMethodCollection $collection)
 *
 * @extends ReflectionMemberCollection<ReflectionMethod>
 */
interface ReflectionMethodCollection extends ReflectionMemberCollection
{
    public function abstract();
}
