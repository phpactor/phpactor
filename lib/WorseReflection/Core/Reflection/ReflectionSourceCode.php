<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;

interface ReflectionSourceCode
{
    public function position(): ByteOffsetRange;

    public function findClass(ClassName $name);
}
