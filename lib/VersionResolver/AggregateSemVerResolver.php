<?php

namespace Phpactor\VersionResolver;

use Amp\Promise;
use function Amp\call;

class AggregateSemVerResolver implements SemVersionResolver
{
    /** @var SemVersionResolver[] */
    private array $resolvers;

    public function __construct(
        SemVersionResolver ...$resolvers,
    ) {
        $this->resolvers = $resolvers;
    }

    /**
     * @return Promise<?SemVersion>
     */
    public function resolve(): Promise
    {
        return call(function () {
            foreach ($this->resolvers as $resolver) {
                $version = yield $resolver->resolve();
                if (null !== $version) {
                    return $version;
                }
            }

            return null;
        });
    }
}
