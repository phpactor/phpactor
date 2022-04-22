<?php

namespace Phpactor\WorseReflection\Core\TypeResolver;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeResolver;

class PassthroughTypeResolver implements TypeResolver
{
    public function resolve(Type $type): Type
    {
        return $type;
    }
}
