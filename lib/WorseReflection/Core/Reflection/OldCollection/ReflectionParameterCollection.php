<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;

/**
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
