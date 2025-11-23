<?php

namespace Phpactor\VersionResolver;

use Composer\Semver\Comparator;

class SemVersion
{
    public function __construct(
        private string $version,
    ) {
    }

    public function __toString(): string
    {
        return $this->version;
    }

    public function greaterThanOrEqualTo(SemVersion $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->version, $version->__toString());
    }
}
