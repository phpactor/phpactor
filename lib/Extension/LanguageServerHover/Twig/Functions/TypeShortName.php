<?php

namespace Phpactor\Extension\LanguageServerHover\Twig\Functions;

use Phpactor\WorseReflection\Core\Type;

class TypeShortName
{
    public function __invoke(Type $type): string
    {
        return $type->__toString();
    }
}
