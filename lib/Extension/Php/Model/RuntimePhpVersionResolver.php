<?php

namespace Phpactor\Extension\Php\Model;

use Phpactor\Extension\Php\Model\PhpVersionResolver;

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
