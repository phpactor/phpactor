<?php

namespace Phpactor\Extension\Php\Model;

class RuntimePhpVersionResolver implements PhpVersionResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(): ?string
    {
        return phpversion();
    }
}
