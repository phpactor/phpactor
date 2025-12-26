<?php

namespace Phpactor\VersionResolver;

use Amp\Promise;
use Amp\Success;

class ArbitrarySemVerResolver implements SemVersionResolver
{
    public function __construct(
        private readonly ?string $version = null,
    ) {
    }

    /**
     * @return Promise<?SemVersion>
     */
    public function resolve(): Promise
    {
        return new Success((null === $this->version) ? null : SemVersion::fromString($this->version));
    }
}
