<?php

namespace Phpactor\Extension\Php\Model;

class RuntimePhpVersionResolver implements PhpVersionResolver
{
    public function resolve(): ?string
    {
        return phpversion();
    }
}
