<?php

namespace Phpactor\Extension\LanguageServerHover\Twig;

use Phpactor\Extension\LanguageServerHover\Twig\Function\TypeType;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\TypeUtil;
use Twig\Environment;
use Twig\TwigFunction;

final class TwigFunctions
{
    public static function add(Environment $env): Environment
    {
        $env->addFunction(new TwigFunction('typeShortName', function (Type $type) {
            if ($type instanceof ReflectedClassType) {
                return TypeUtil::toLocalType($type);
            }

            return $type->__toString();
        }));

        $env->addFunction(new TwigFunction('typeDefined', function (Type $type) {
            return TypeUtil::isDefined($type);
        }));
        $env->addFunction(new TwigFunction('typeType', new TypeType()));

        return $env;
    }
}
