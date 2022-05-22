<?php

namespace Phpactor\WorseReflection\Core\Reflection\OldCollection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

/**
 * @method ReflectionProperty first()
 * @method ReflectionProperty last()
 * @method ReflectionProperty get(string $name)
 * @method ReflectionPropertyCollection merge(ReflectionPropertyCollection $collection)
 *
 * @extends ReflectionMemberCollection<ReflectionProperty>
 */
interface ReflectionPropertyCollection extends ReflectionMemberCollection
{
}
