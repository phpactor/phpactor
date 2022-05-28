<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class ListType extends ArrayType
{
    public function __construct(?Type $valueType = null)
    {
        parent::__construct(new IntType(), $valueType ?: new MixedType());
    }
}
