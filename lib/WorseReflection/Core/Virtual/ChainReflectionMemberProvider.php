<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\Collection\ClassLikeReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;

class ChainReflectionMemberProvider implements ReflectionMemberProvider
{
    /**
     * TODO: make private when finished refactoring
     *
     * @var ReflectionMemberProvider[]
     */
    public array $providers;

    public function __construct(ReflectionMemberProvider ...$providers)
    {
        $this->providers = $providers;
    }

    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $virtualMethods = ClassLikeReflectionMemberCollection::empty();
        foreach ($this->providers as $provider) {
            /** @phpstan-ignore-next-line */
            $virtualMethods = $virtualMethods->merge($provider->provideMembers($locator, $class));
        }

        return $virtualMethods;
    }
}
