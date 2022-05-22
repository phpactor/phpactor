<?php

namespace Phpactor\WorseReflection\Core\Reflection\OldCollection;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionParameterCollection;

/**
 * @method ReflectionParameter first()
 * @method ReflectionParameter last()
 * @method ReflectionParameter get(string $name)
 * @extends ReflectionCollection<ReflectionParameter>
 */
interface ReflectionParameterCollection extends ReflectionCollection
{
    /**
     * @return ReflectionParameterCollection<ReflectionParameter>
     */
    public function notPromoted(): ReflectionParameterCollection;

    /**
     * @return ReflectionParameterCollection<ReflectionParameter>
     */
    public function promoted(): ReflectionParameterCollection;
}
