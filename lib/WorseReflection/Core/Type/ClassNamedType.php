<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;

interface ClassNamedType
{
    public function name(): ClassName;
}
