<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod as CoreReflectionMethod;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;

/**
 * @extends HomogeneousReflectionMemberCollection<CoreReflectionMethod>
 */
class ReflectionMethodCollection extends HomogeneousReflectionMemberCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class, ReflectionClassLike $reflectionClass): self
    {
        /** @var MethodDeclaration[] $methods */
        $methods = array_filter($class->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $reflectionClass, $method);
        }

        return new self($items);
    }

    public static function fromEnumDeclaration(ServiceLocator $serviceLocator, EnumDeclaration $class, ReflectionClassLike $reflectionClass): self
    {
        /** @var MethodDeclaration[] $methods */
        $methods = array_filter($class->enumMembers->enumMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $reflectionClass, $method);
        }

        return new self($items);
    }

    public static function fromInterfaceDeclaration(ServiceLocator $serviceLocator, InterfaceDeclaration $interface, ReflectionClassLike $reflectionInterface): CoreReflectionMethodCollection
    {
        /** @var MethodDeclaration[] $methods */
        $methods = array_filter($interface->interfaceMembers->interfaceMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $reflectionInterface, $method);
        }

        return new self($items);
    }

    public static function fromTraitDeclaration(ServiceLocator $serviceLocator, TraitDeclaration $trait, ReflectionClassLike $contextClass): CoreReflectionMethodCollection
    {
        /** @var MethodDeclaration[] $methods */
        $methods = array_filter($trait->traitMembers->traitMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $contextClass, $method);
        }

        return new self($items);
    }

    /**
     * @param CoreReflectionMethod[] $methods
     */
    public static function fromReflectionMethods(array $methods): CoreReflectionMethodCollection
    {
        $items = [];
        foreach ($methods as $method) {
            $items[$method->name()] = $method;
        }
        return new self($items);
    }

    public function abstract(): CoreReflectionMethodCollection
    {
        return new self(array_filter($this->items, function (CoreReflectionMethod $item) {
            return $item->isAbstract();
        }));
    }
}
