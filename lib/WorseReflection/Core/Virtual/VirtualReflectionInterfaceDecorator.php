<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Visibility;

class VirtualReflectionInterfaceDecorator extends VirtualReflectionClassLikeDecorator implements ReflectionInterface
{
    private ReflectionInterface $interface;

    /**
     * @var ReflectionMemberProvider[]
     */
    private array $memberProviders;
    
    private ServiceLocator $serviceLocator;

    public function __construct(ServiceLocator $serviceLocator, ReflectionInterface $interface, array $memberProviders = [])
    {
        parent::__construct($interface);
        $this->interface = $interface;
        $this->memberProviders = $memberProviders;
        $this->serviceLocator = $serviceLocator;
    }

    public function constants(): ReflectionConstantCollection
    {
        return $this->interface->constants();
    }

    public function parents(): ReflectionInterfaceCollection
    {
        return $this->interface->parents();
    }

    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection
    {
        $realMethods = $this->interface->methods($contextClass);
        $virtualMethods = $this->virtualMethods();

        $methods = $realMethods->merge($virtualMethods);
        assert($methods instanceof ReflectionMethodCollection);
        return $methods;
    }

    public function members(): ReflectionMemberCollection
    {
        $members = $this->interface->members();
        $members->merge($this->virtualMethods());
        return $members;
    }

    public function virtualMethods(): VirtualReflectionMethodCollection
    {
        $virtualMethods = VirtualReflectionMethodCollection::fromReflectionMethods([]);

        foreach ($this->parents() as $interface) {
            assert($interface instanceof VirtualReflectionInterfaceDecorator);
            $virtualMethods = $virtualMethods->merge(
                $interface->virtualMethods()->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        foreach ($this->memberProviders as $memberProvider) {
            $virtualMethods = $virtualMethods->merge(
                $memberProvider->provideMembers($this->serviceLocator, $this->interface)->methods()
            );
        }

        return $virtualMethods;
    }
}
