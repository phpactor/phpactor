<?php

namespace Phpactor\Extension\Php\Model;

class ConstantPhpVersionResolver implements PhpVersionResolver
{
    public function __construct(private readonly ?string $version)
    {
    }


    public function resolve(): ?string
    {
        return $this->version;
    }

    public function name(): string
    {
        return 'user configured';
    }
}
