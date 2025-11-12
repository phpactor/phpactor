<?php

namespace Phpactor\VersionResolver;

class ArbitrarySemVerResolver implements SemVersionResolver
{
    public function __construct(
        private ?string $version = null,
    ) {
    }

    public function resolve(): ?SemVersion
    {
        if (null === $this->version) {
            return null;
        }

        return new SemVersion($this->version);
    }
}
