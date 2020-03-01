<?php

namespace Phpactor\Extension\Php\Model;

use RuntimeException;

class ChainResolver implements PhpVersionResolver
{
    /**
     * @var PhpVersionResolver[]
     */
    private $versionResolvers;

    public function __construct(PhpVersionResolver ...$versionResolvers)
    {
        $this->versionResolvers = $versionResolvers;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(): ?string
    {
        foreach ($this->versionResolvers as $versionResolver) {
            if (!$version = $versionResolver->resolve()) {
                continue;
            }

            return $version;
        }

        throw new RuntimeException(sprintf(
            '%s resolvers could not resolve PHP version',
            count($this->versionResolvers)
        ));
    }
}
