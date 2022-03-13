<?php

namespace Phpactor\Extension\Rpc\Registry;

use Phpactor\Extension\Rpc\Exception\HandlerNotFound;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\HandlerRegistry;
use Psr\Container\ContainerInterface;

class LazyContainerHandlerRegistry implements HandlerRegistry
{
    private array $serviceMap;

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, array $serviceMap)
    {
        $this->serviceMap = $serviceMap;
        $this->container = $container;
    }

    public function get($handlerName): Handler
    {
        if (!isset($this->serviceMap[$handlerName])) {
            if (false === isset($this->serviceMap[$handlerName])) {
                throw new HandlerNotFound(sprintf(
                    'No handler "%s", available handlers: "%s"',
                    $handlerName,
                    implode('", "', array_keys($this->serviceMap))
                ));
            }
        }

        return $this->container->get($this->serviceMap[$handlerName]);
    }
}
