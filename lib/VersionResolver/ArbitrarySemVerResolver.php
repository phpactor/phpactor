<?php

namespace Phpactor\VersionResolver;

use Amp\Promise;

use function Amp\call;

class ArbitrarySemVerResolver implements SemVersionResolver
{
    public function __construct(
        private ?string $version = null,
    ) {
    }

    /**
     * @return Promise<?SemVersion>
     */
    public function resolve(): Promise
    {
        return call(function () {
            if (null === $this->version) {
                return null;
            }

            return new SemVersion($this->version);
        });
    }
}
