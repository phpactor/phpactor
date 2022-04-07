<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface Generalizable
{
    public function generalize(): Type;
}
