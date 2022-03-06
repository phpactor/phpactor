<?php

namespace Phpactor\Extension\Php\Model;

class ConstantPhpVersionResolver implements PhpVersionResolver
{
    /**
     * @var string|null
     */
    private $version;

    public function __construct(?string $version)
    {
        $this->version = $version;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(): ?string
    {
        return $this->version;
    }
}
