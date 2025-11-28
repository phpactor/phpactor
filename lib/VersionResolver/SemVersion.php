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

    public function greaterThanOrEqualTo(SemVersion $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->version, $version->__toString());
    }

    public function lessThan(SemVersion $version): bool
    {
        return Comparator::lessThan($this->version, $version->__toString());
    }
}
