<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;

interface ReflectionSourceCode
{
    public function position(): Position;

    public function findClass(ClassName $name);
}
