<?php

namespace Phpactor\VersionResolver;

class CompoundSemVerResolver implements SemVersionResolver
{
    /** @var SemVersionResolver[] */
    private array $resolvers;

    public function __construct(
        SemVersionResolver ...$resolvers,
    ) {
        $this->resolvers = $resolvers;
    }

    public function resolve(): ?SemVersion
    {
        foreach ($this->resolvers as $resolver) {
            $version = $resolver->resolve();
            if (null !== $version) {
                return $version;
            }
        }

        return null;
    }
}
