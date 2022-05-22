<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant get(string $name)
 * @method ReflectionConstantCollection merge(ReflectionConstantCollection $collection)
 * @extends ReflectionMemberCollection<ReflectionConstant>
 */
interface ReflectionConstantCollection extends ReflectionMemberCollection
{
}
