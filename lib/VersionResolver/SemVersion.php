<?php

namespace Phpactor\VersionResolver;

use Composer\Semver\Comparator;

class SemVersion
{
    public function __construct(
        private string $version,
    ) {
    }

    public function greaterThanOrEqualTo(string $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->version, $version);
    }
}
