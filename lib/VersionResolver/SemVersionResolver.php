<?php

namespace Phpactor\VersionResolver;

interface SemVersionResolver
{
    public function resolve(): ?SemVersion;
}
