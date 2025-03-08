<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\VoidType;

class WorseTypeRenderer70 implements WorseTypeRenderer
{
    public function render(Type $type): ?string
    {
        if ($type instanceof VoidType) {
            return null;
        }

        return null;
    }
}
