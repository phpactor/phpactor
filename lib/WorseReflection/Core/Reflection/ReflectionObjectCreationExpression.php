<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;

use Phpactor\TextDocument\ByteOffsetRange;

interface ReflectionObjectCreationExpression extends ReflectionNode
{
    public function position(): ByteOffsetRange;

    public function class(): ReflectionClassLike;

    public function arguments(): ReflectionArgumentCollection;
}
