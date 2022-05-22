<?php

namespace Phpactor\Extension\PhpSpec\Provider;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;

class ObjectBehaviorMemberProvider implements ReflectionMemberProvider
{
    private const OBJECT_BEHAVIOR_CLASS = '\PhpSpec\ObjectBehavior';
    private const SUBJECT_CLASS = '\PhpSpec\Wrapper\Subject';
    
    private string $specPrefix;

    public function __construct(string $specPrefix = 'spec')
    {
        $this->specPrefix = $specPrefix;
    }

    public function provideMembers(
        ServiceLocator $serviceLocator,
        ReflectionClassLike $class
    ): ReflectionMemberCollection {
        $subjectClassName = explode('\\', $class->name()->namespace());

        if (false === $this->isSpecCandidate($class, $subjectClassName)) {
            return VirtualReflectionMemberCollection::fromMembers([]);
        }

        array_shift($subjectClassName);
        $subjectClassName[] = substr($class->name()->short(), 0, -4);
        $subjectClassName = implode('\\', $subjectClassName);

        try {
            $subjectClass = $serviceLocator->reflector()->reflectClass($subjectClassName);
        } catch (NotFound $e) {
            $serviceLocator->logger()->warning(sprintf(
                'Phpspec extension could not locate inferred class name "%s" '.
                'for spec class "%s": %s',
                $class->name()->full(),
                $subjectClassName,
                $e->getMessage()
            ));
            return VirtualReflectionMethodCollection::fromMembers([]);
        }

        $virtualMethods = [];
        foreach ($subjectClass->methods() as $subjectMethod) {
            $method = VirtualReflectionMethod::fromReflectionMethod($subjectMethod);
            $subjectType = TypeFactory::reflectedClass($serviceLocator->reflector(), self::SUBJECT_CLASS);
            $method = $method
                ->withInferredType($method->inferredType()->addType($subjectType))
                ->withType($subjectType)
            ;
            $virtualMethods[] = $method;
        }

        return VirtualReflectionMemberCollection::fromMembers($virtualMethods);
    }

    /**
     * @param array<string> $subjectClassName
     */
    private function isSpecCandidate(ReflectionClassLike $class, array $subjectClassName): bool
    {
        if (!$class->isInstanceOf(ClassName::fromString(self::OBJECT_BEHAVIOR_CLASS))) {
            return false;
        }
        
        if (array_shift($subjectClassName) !== $this->specPrefix) {
            return false;
        }
        
        $suffix = substr($class->name()->short(), -4);
        if ('Spec' !== $suffix) {
            return false;
        }

        return true;
    }
}
