<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;

interface TemplateNameResolver
{
    public function resolveName(Prototype $prototype): string;
}
