<?php

namespace Phpactor\Extension\LanguageServerHover\Twig\Function;

use Phpactor\WorseReflection\TypeUtil;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type;

class TypeShortName
{
    public function __invoke(Type $type): string
    {
        if ($type instanceof ReflectedClassType) {
            return TypeUtil::toLocalType($type)->__toString();
        }

        return $type->__toString();
    }
}
