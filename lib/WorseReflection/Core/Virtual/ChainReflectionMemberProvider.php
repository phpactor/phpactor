<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;

class ChainReflectionMemberProvider implements ReflectionMemberProvider
{
    /**
     * @var ReflectionMemberProvider[]
     */
    private array $providers;

    public function __construct(ReflectionMemberProvider ...$providers) {
        $this->providers = $providers;
    }

    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $virtualMethods = ReflectionMethodCollection::fromReflectionMethods([]);
        foreach ($this->providers as $provider) {
            $virtualMethods = $virtualMethods->merge($provider->provideMembers($locator, $class););
        }

        return $virtualMethods;
    }
}
