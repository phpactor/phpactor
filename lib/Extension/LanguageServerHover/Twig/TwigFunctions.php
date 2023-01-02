<?php

namespace Phpactor\Extension\LanguageServerHover\Twig;

use Phpactor\DocblockParser\Ast\Node;
use Phpactor\Extension\LanguageServerHover\Twig\Functions\TypeShortName;
use Phpactor\Extension\LanguageServerHover\Twig\Functions\TypeType;
use Phpactor\Extension\ObjectRenderer\Extension\ObjectRendererTwigExtension;
use Phpactor\WorseReflection\Core\Type;
use Twig\Environment;
use Twig\TwigFunction;

final class TwigFunctions implements ObjectRendererTwigExtension
{
    public function configure(Environment $env): void
    {
        $env->addFunction(new TwigFunction('docblockNode',function (Node $node) {
            return $node->toString();
        }));
        $env->addFunction(new TwigFunction('typeShortName', new TypeShortName()));

        $env->addFunction(new TwigFunction('typeDefined', function (Type $type) {
            return ($type->isDefined());
        }));
        $env->addFunction(new TwigFunction('class', function ($type) {
            return get_class($type);
        }));
        $env->addFunction(new TwigFunction('dump', function ($type) {
            return dump($type);
        }));
        $env->addFunction(new TwigFunction('typeType', new TypeType()));
    }
}
