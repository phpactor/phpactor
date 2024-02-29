<?php

namespace Phpactor\Extension\Laravel\Providers;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;

class LaravelModelPropertiesProvider implements ReflectionMemberProvider
{
    public function __construct(private LaravelContainerInspector $laravelContainer)
    {
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $className = $class->name()->__toString();

        $modelsData = $this->laravelContainer->models();

        if (!isset($modelsData[$className])) {
            return ChainReflectionMemberCollection::fromCollections([]);
        }

        return $this->laravelContainer->getMethodsAndPropertiesForClass(
            $class,
            $class,
            $modelsData[$className],
            $locator->reflector()
        );
    }
}
