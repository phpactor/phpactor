<?php

namespace Phpactor\Extension\LanguageServer\EventDispatcher;

use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use RuntimeException;

class LazyAggregateProvider implements ListenerProviderInterface
{
    private ?ListenerProviderAggregate $aggregateProvider = null;

    /**
     * @param list<string> $serviceIds
     */
    public function __construct(private ContainerInterface $container, private array $serviceIds)
    {
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if (null === $this->aggregateProvider) {
            $this->aggregateProvider = new ListenerProviderAggregate();
            foreach ($this->serviceIds as $serviceId) {
                /** @var object|null $listenerProvider */
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
