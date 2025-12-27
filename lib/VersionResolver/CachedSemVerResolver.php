<?php

namespace Phpactor\VersionResolver;

use Amp\Promise;

use Psr\Log\LoggerInterface;
use function Amp\call;

class CachedSemVerResolver implements SemVersionResolver
{
    private ?SemVersion $version;

    public function __construct(
        private readonly SemVersionResolver $resolver,
        private readonly LoggerInterface $logger,
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

            if (null !== $this->version) {
                $this->logger->info(sprintf(
                    'resolved version "%s"',
                    $this->version->__toString()
                ));
            }

            return $this->version;
        });
    }
}
