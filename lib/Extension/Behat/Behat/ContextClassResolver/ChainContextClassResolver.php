<?php

namespace Phpactor\Extension\Behat\Behat\ContextClassResolver;

use Phpactor\Extension\Behat\Behat\ContextClassResolver;
use Phpactor\Extension\Behat\Behat\Exception\CouldNotResolverContextClass;

class ChainContextClassResolver implements ContextClassResolver
{
    /**
     * @var ContextClassResolver[]
     */
    private array $contextClassResolvers;

    /**
     * @param ContextClassResolver[] $contextClassResolvers
     */
    public function __construct(array $contextClassResolvers)
    {
        $this->contextClassResolvers = $contextClassResolvers;
    }

    public function resolve(string $className): string
    {
        foreach ($this->contextClassResolvers as $resolver) {
            try {
                return $resolver->resolve($className);
            } catch (CouldNotResolverContextClass $couldNot) {
            }
        }

        throw new CouldNotResolverContextClass(sprintf(
            'Could not resolve context class for "%s"',
            $className
        ));
    }
}
