<?php

namespace Phpactor\VersionResolver;

class CachedSemVerResolver implements SemVersionResolver
{
    private ?SemVersion $version;

    public function __construct(
        private SemVersionResolver $resolver,
    ) {
    }

    public function resolve(): ?SemVersion
    {
        if (isset($this->version)) {
            return $this->version;
        }

        $this->version = $this->resolver->resolve();

        return $this->version;
    }
}
