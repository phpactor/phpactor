<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;

final class ClassShortNameResolver implements TemplateNameResolver
{
    public function resolveName(Prototype $prototype): string
    {
        return basename(str_replace('\\', '/', get_class($prototype))) . '.php.twig';
    }
}
