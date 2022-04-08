<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface Concatable
{
    public function concat(Type $right): Type;
}
