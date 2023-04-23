<?php

namespace Phpactor\Extension\Laravel\Providers;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;

class LaravelQueryBuilderProvider implements ReflectionMemberProvider
{
    public function __construct(private LaravelContainerInspector $laravelContainer)
    {
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(
        ServiceLocator $locator,
        ReflectionClassLike $builderClass
    ): ReflectionMemberCollection {
        $builderClassName = $builderClass->name()->__toString();
        if (str_starts_with($builderClassName, 'Laravel') && str_ends_with($builderClassName, 'VirtualBuilder')) {
            $type = $builderClass->templateMap()->get('TModelClass');
            if ($type instanceof MissingType || !($type instanceof ClassType)) {
                return ChainReflectionMemberCollection::fromCollections([]);
            }

            if ($modelData = $this->laravelContainer->models()[$type->name()->__toString()] ?? false) {
                $class = $locator->reflector()->reflectClass($type->name());

                $relationBuilder = new GenericClassType($locator->reflector(), $builderClass->name(), [$class->type()]);

                return $this->laravelContainer->getMethodsAndPropertiesForClass(
                    $builderClass,
                    $locator->reflector()->reflectClass($type->name()),
                    $modelData,
                    $locator->reflector()
                );
            }
        }

        return ChainReflectionMemberCollection::fromCollections([]);
    }
}
