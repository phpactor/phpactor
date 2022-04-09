<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

abstract class NumericType extends ScalarType
{
    public function identity(): NumericType
    {
        return $this;
    }

    public function negative(): NumericType
    {
        return $this;
    }
}
