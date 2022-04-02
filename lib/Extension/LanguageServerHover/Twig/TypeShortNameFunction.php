<?php

namespace Phpactor\Extension\LanguageServerHover\Twig;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\TypeUtil;
use Twig\Environment;
use Twig\TwigFunction;

final class TypeShortNameFunction
{
    public static function add(Environment $env): Environment
    {
        $env->addFunction(new TwigFunction('typeShortName', function (Type $type) {
            if ($type instanceof ReflectedClassType) {
                return TypeUtil::toLocalType($type);
            }

            return $type->__toString();
        }));

        return $env;
    }
}
