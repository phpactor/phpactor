<?php

namespace Phpactor\Extension\PhpSpec\Provider;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\HomogeneousReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;

class ObjectBehaviorMemberProvider implements ReflectionMemberProvider
{
    private const OBJECT_BEHAVIOR_CLASS = '\PhpSpec\ObjectBehavior';
    private const SUBJECT_CLASS = '\PhpSpec\Wrapper\Subject';

    private string $specPrefix;

    public function __construct(string $specPrefix = 'spec')
    {
        $this->specPrefix = $specPrefix;
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function provideMembers(
        ServiceLocator $serviceLocator,
        ReflectionClassLike $specClass
    ): ReflectionMemberCollection {
        $specClassName = explode('\\', $specClass->name()->namespace());

        if (false === $this->isSpecCandidate($specClass, $specClassName)) {
            return HomogeneousReflectionMemberCollection::fromMembers([]);
        }

        array_shift($specClassName);
        $specClassName[] = substr($specClass->name()->short(), 0, -4);
        $specClassName = implode('\\', $specClassName);

        try {
            $testedClass = $serviceLocator->reflector()->reflectClass($specClassName);
        } catch (NotFound $e) {
            $serviceLocator->logger()->warning(sprintf(
                'Phpspec extension could not locate inferred class name "%s" ' .
                    'for spec class "%s": %s',
                $specClass->name()->full(),
                $specClassName,
                $e->getMessage()
            ));
            return HomogeneousReflectionMemberCollection::fromMembers([]);
        }

        $subjectType = TypeFactory::reflectedClass($serviceLocator->reflector(), self::SUBJECT_CLASS);

        return HomogeneousReflectionMemberCollection::fromMembers(array_merge(
            $this->retrievePublicMethods($testedClass, $subjectType),
            $this->retrievePublicProperties($testedClass, $subjectType),
        ));
    }

    /**
     * @param array<string> $specClassName
     */
    private function isSpecCandidate(ReflectionClassLike $specClass, array $specClassName): bool
    {
        if (!$specClass->isInstanceOf(ClassName::fromString(self::OBJECT_BEHAVIOR_CLASS))) {
            return false;
        }

        if (array_shift($specClassName) !== $this->specPrefix) {
            return false;
        }

        $suffix = substr($specClass->name()->short(), -4);
        if ('Spec' !== $suffix) {
            return false;
        }

        return true;
    }

    /**
     * @return array<VirtualReflectionMethod>
     */
    private function retrievePublicMethods(ReflectionClass $testedClass, ReflectedClassType $subjectType): array
    {
        $virtualMethods = [];
        foreach ($testedClass->methods() as $method) {
            if (false === $method->visibility()->isPublic()) {
                continue;
            }

            $virtualMethod = VirtualReflectionMethod::fromReflectionMethod($method);
            $virtualMethods[] = $virtualMethod
                ->withInferredType(TypeFactory::intersection($virtualMethod->inferredType(), $subjectType))
                ->withType($subjectType);
        }

        return $virtualMethods;
    }

    /**
     * @return array<VirtualReflectionProperty>
     */
    private function retrievePublicProperties(ReflectionClass $testedClass, ReflectedClassType $subjectType): array
    {
        $virtualProperties = [];
        foreach ($testedClass->properties() as $property) {
            if (false === $property->visibility()->isPublic()) {
                continue;
            }

            $virtualProperty = VirtualReflectionProperty::fromReflectionProperty($property);
            $virtualProperties[] = $virtualProperty
                ->withInferredType(TypeFactory::intersection($virtualProperty->inferredType(), $subjectType))
                ->withType($subjectType);
        }

        return $virtualProperties;
    }
}
