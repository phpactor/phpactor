<?php

namespace Phpactor\Extension\Php\Model;

class ConstantPhpVersionResolver implements PhpVersionResolver
{
    private ?string $version;

    public function __construct(?string $version)
    {
        $this->version = $version;
    }

    
    public function resolve(): ?string
    {
        return $this->version;
    }
}
