<?php

namespace Phpactor\Extension\Behat\Adapter\Worse;

use Phpactor\Extension\Behat\Behat\ContextClassResolver;
use Phpactor\Extension\Behat\Behat\Exception\CouldNotResolverContextClass;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;

final class WorseContextClassResolver implements ContextClassResolver
{
    /**
     * @var ClassReflector
     */
    private $reflector;

    public function __construct(ClassReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function resolve(string $className): string
    {
        try {
            $this->reflector->reflectClass($className);
        } catch (NotFound $notFound) {
            throw new CouldNotResolverContextClass($notFound->getMessage(), 0, $notFound);
        }

        return $className;
    }
}
