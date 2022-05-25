<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\StaticType;

class WorseTypeRenderer81 extends WorseTypeRenderer80
{
    public function render(Type $type): ?string
    {
        if ($type instanceof IntersectionType) {
            return $type->short();
        }

        if ($type instanceof StaticType) {
            return $type->toPhpString();
        }

        return parent::render($type);
    }
}
