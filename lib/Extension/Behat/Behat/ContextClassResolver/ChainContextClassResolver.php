<?php

namespace Phpactor\Extension\Behat\Behat\ContextClassResolver;

use Phpactor\Extension\Behat\Behat\ContextClassResolver;
use Phpactor\Extension\Behat\Behat\Exception\CouldNotResolverContextClass;

class ChainContextClassResolver implements ContextClassResolver
{
    /**
     * @param ContextClassResolver[] $contextClassResolvers
     */
    public function __construct(private readonly array $contextClassResolvers)
    {
    }

    public function resolve(string $className): string
    {
        foreach ($this->contextClassResolvers as $resolver) {
            try {
                return $resolver->resolve($className);
            } catch (CouldNotResolverContextClass) {
            }
        }

        throw new CouldNotResolverContextClass(sprintf(
            'Could not resolve context class for "%s"',
            $className
        ));
    }
}
