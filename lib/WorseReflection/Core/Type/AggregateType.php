<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

/**
 * @template T of Type
 */
interface AggregateType
{
    /**
     * @return Types<T>
     */
    public function toTypes(): Types;

    public function narrowTo(Type $narrowTypes): Type;
}
