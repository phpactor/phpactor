<?php

namespace Phpactor\VersionResolver;

use Amp\Promise;

use function Amp\call;

class CachedSemVerResolver implements SemVersionResolver
{
    private ?SemVersion $version;

    public function __construct(
        private SemVersionResolver $resolver,
    ) {
    }

    /**
     * @return Promise<?SemVersion>
     */
    public function resolve(): Promise
    {
        return call(function () {
            if (isset($this->version)) {
                return $this->version;
            }

            $this->version = yield $this->resolver->resolve();

            return $this->version;
        });
    }
}
