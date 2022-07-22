<?php

namespace Phpactor\Extension\LanguageServerHover\Twig\Functions;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

class TypeShortName
{
    public function __invoke(Type $type): string
    {
        return TypeUtil::shortenClassTypes($type);
    }
}
