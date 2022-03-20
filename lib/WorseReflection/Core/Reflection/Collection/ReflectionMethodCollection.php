<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMethod first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMethod last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMethod get(string $name)
 *
 * @implements ReflectionMemberCollection<ReflectionMethod>
 */
interface ReflectionMethodCollection extends ReflectionMemberCollection
{
    public function abstract();
}
