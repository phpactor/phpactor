<?php

namespace Phpactor\Extension\LanguageServer\EventDispatcher;

use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use RuntimeException;

class LazyAggregateProvider implements ListenerProviderInterface
{
    private ContainerInterface $container;

    private array $serviceIds;

    private ?ListenerProviderAggregate $aggregateProvider = null;

    public function __construct(ContainerInterface $container, array $serviceIds)
    {
        $this->container = $container;
        $this->serviceIds = $serviceIds;
    }
    
    public function getListenersForEvent(object $event): iterable
    {
        if (null === $this->aggregateProvider) {
            $this->aggregateProvider = new ListenerProviderAggregate();
            foreach ($this->serviceIds as $serviceId) {
                $listenerProvider = $this->container->get($serviceId);

                // if null assume that it was conditionally disabled
                if (null === $listenerProvider) {
                    continue;
                }

                if (!$listenerProvider instanceof ListenerProviderInterface) {
                    throw new RuntimeException(sprintf(
                        'Listener service with ID "%s" must implement ListenerProviderInterface, it is of class "%s"',
                        $serviceId,
                        get_class($listenerProvider)
                    ));
                }

                $this->aggregateProvider->attach($listenerProvider);
            }
        }

        return $this->aggregateProvider->getListenersForEvent($event);
    }
}
