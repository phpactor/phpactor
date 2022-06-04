<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\NullType;

class WorseTypeRenderer82 extends WorseTypeRenderer81
{
    public function render(Type $type): ?string
    {
        if ($type instanceof NullType) {
            return $type->toPhpString();
        }

        if ($type instanceof FalseType) {
            return $type->toPhpString();
        }

        return parent::render($type);
    }
}
