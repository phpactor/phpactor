<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;

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
