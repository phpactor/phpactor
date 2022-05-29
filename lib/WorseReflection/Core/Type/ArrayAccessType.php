<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface ArrayAccessType
{
    /**
     * @param array-key $offset $offset
     */
    public function typeAtOffset($offset): Type;
}
