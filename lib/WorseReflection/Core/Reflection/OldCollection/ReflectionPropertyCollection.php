<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

/**
 * @method ReflectionProperty first()
 * @method ReflectionProperty last()
 * @method ReflectionProperty get(string $name)
 *
 * @extends ReflectionMemberCollection<ReflectionProperty>
 */
interface ReflectionPropertyCollection extends ReflectionMemberCollection
{
}
