<?php

namespace Phpactor\Extension\LanguageServerHover\Twig;

use Phpactor\Extension\LanguageServerHover\Twig\Functions\TypeShortName;
use Phpactor\Extension\LanguageServerHover\Twig\Functions\TypeType;
use Phpactor\WorseReflection\Core\Type;
use Twig\Environment;
use Twig\TwigFunction;

final class TwigFunctions
{
    public static function add(Environment $env): Environment
    {
        $env->addFunction(new TwigFunction('typeShortName', new TypeShortName()));

        $env->addFunction(new TwigFunction('typeDefined', function (Type $type) {
            return ($type->isDefined());
        }));
        $env->addFunction(new TwigFunction('class', function (Type $type) {
            return get_class($type);
        }));
        $env->addFunction(new TwigFunction('typeType', new TypeType()));

        return $env;
    }
}
