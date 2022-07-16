<?php

namespace Phpactor\WorseReflection\Core\Reflection;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;

use Phpactor\WorseReflection\Core\Position;

interface ReflectionObjectCreationExpression extends ReflectionNode
{
    public function position(): Position;

    public function class(): ReflectionClassLike;

    public function arguments(): ReflectionArgumentCollection;
}
