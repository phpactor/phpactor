<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class WorseTypeRenderer81 extends WorseTypeRenderer80
{
    public function render(Type $type): ?string
    {
        if ($type instanceof UnionType) {
            return $type->toPhpString();
        }

        if ($type instanceof IntersectionType) {
            return $type->toPhpString();
        }

        if ($type instanceof StaticType) {
            return $type->toPhpString();
        }

        return parent::render($type);
    }
}
