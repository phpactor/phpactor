<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Position;

interface ReflectionSourceCode
{
    public function position(): Position;

    public function findClass(ClassName $name);
}
