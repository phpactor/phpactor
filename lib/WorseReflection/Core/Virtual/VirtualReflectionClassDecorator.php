<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Visibility;

class VirtualReflectionClassDecorator extends VirtualReflectionClassLikeDecorator implements ReflectionClass
{
    private ReflectionClass $class;

    /**
     * @var ReflectionMemberProvider[]
     */
    private array $memberProviders;

    private ServiceLocator $serviceLocator;

    public function __construct(ServiceLocator $serviceLocator, ReflectionClass $class, array $memberProviders = [])
    {
        parent::__construct($class);
        $this->class = $class;
        $this->memberProviders = $memberProviders;
        $this->serviceLocator = $serviceLocator;
    }

    public function isAbstract(): bool
    {
        return $this->class->isAbstract();
    }

    public function constants(): ReflectionConstantCollection
    {
        return $this->class->constants();
    }

    public function parent(): ?ReflectionClass
    {
        return $this->class->parent();
    }

    public function properties(ReflectionClassLike $contextClass = null): ReflectionPropertyCollection
    {
        $realProperties = $this->class->properties($contextClass ?: $this->class);
        $virtualProperties = $this->virtualProperties();

        return $realProperties->merge($virtualProperties);
    }

    public function interfaces(): ReflectionInterfaceCollection
    {
        return $this->class->interfaces();
    }

    public function traits(): ReflectionTraitCollection
    {
        return $this->class->traits();
    }

    public function memberListPosition(): Position
    {
        return $this->class->memberListPosition();
    }

    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection
    {
        $realMethods = $this->class->methods($contextClass ?: $this->class);
        $virtualMethods = $this->virtualMethods();

        $merged = $realMethods->merge($virtualMethods);
        assert($merged instanceof ReflectionMethodCollection);
        return $merged;
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection
    {
        $members = $this->class->members();
        $members = $members->merge($this->virtualMethods());

        assert($members instanceof ReflectionMemberCollection);
        return $members;
    }

    public function virtualMethods(): VirtualReflectionMethodCollection
    {
        $virtualMethods = VirtualReflectionMethodCollection::fromReflectionMethods([]);
        if ($parentClass = $this->parent()) {
            assert($parentClass instanceof VirtualReflectionClassDecorator);
            $virtualMethods = $virtualMethods->merge(
                $parentClass->virtualMethods()->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        foreach ($this->interfaces() as $interface) {
            assert($interface instanceof VirtualReflectionInterfaceDecorator);
            $virtualMethods = $virtualMethods->merge(
                $interface->virtualMethods()->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        foreach ($this->memberProviders as $memberProvider) {
            $virtualMethods = $virtualMethods->merge(
                $memberProvider->provideMembers($this->serviceLocator, $this->class)->methods()
            );
            foreach ($this->traits() as $trait) {
                $virtualMethods = $virtualMethods->merge(
                    $memberProvider->provideMembers($this->serviceLocator, $trait)->methods()
                );
            }
        }

        return $virtualMethods;
    }

    public function ancestors(): ReflectionClassCollection
    {
        return $this->class->ancestors();
    }

    public function isFinal(): bool
    {
        return $this->class->isFinal();
    }

    private function virtualProperties()
    {
        $virtualProperties = VirtualReflectionPropertyCollection::fromReflectionProperties([]);
        if ($parentClass = $this->parent()) {
            assert($parentClass instanceof VirtualReflectionClassDecorator);
            $virtualProperties = $virtualProperties->merge(
                $parentClass->virtualProperties(
                )->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        foreach ($this->memberProviders as $memberProvider) {
            $virtualProperties = $virtualProperties->merge(
                $memberProvider->provideMembers($this->serviceLocator, $this->class)->properties()
            );

            foreach ($this->traits() as $trait) {
                $virtualProperties = $virtualProperties->merge(
                    $memberProvider->provideMembers($this->serviceLocator, $trait)->properties()
                );
            }
        }

        return $virtualProperties;
    }
}
