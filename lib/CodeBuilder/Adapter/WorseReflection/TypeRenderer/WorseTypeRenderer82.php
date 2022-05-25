<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\NullType;

class WorseTypeRenderer82 extends WorseTypeRenderer80
{
    public function render(Type $type): ?string
    {
        if ($type instanceof NullType) {
            return $type->toPhpString();
        }

        return parent::render($type);
    }
}
