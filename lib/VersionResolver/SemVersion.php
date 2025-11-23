<?php

namespace Phpactor\VersionResolver;

use Composer\Semver\Comparator;

final class SemVersion
{
    private function __construct(
        private string $version,
    ) {
    }

    public function __toString(): string
    {
        return $this->version;
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    public function greaterThanOrEqualTo(SemVersion ...$versions): bool
    {
        foreach  ($versions as $version) {
            if (Comparator::greaterThanOrEqualTo($this->version, $version->__toString())) {
                return true;
            }
        }

        return false;
    }
}
