<?php

namespace Phpactor\Extension\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class FormatterRegistry
{
    private ContainerInterface $container;

    private array $serviceMap;

    public function __construct(ContainerInterface $container, array $serviceMap)
    {
        $this->container = $container;
        $this->serviceMap = $serviceMap;
    }

    public function get(string $alias): FormatterInterface
    {
        if (!isset($this->serviceMap[$alias])) {
            throw new RuntimeException(sprintf(
                'Could not find formatter with alias "%s", known formatters: "%s"',
                $alias,
                implode('", "', array_keys($this->serviceMap))
            ));
        }

        return $this->container->get($this->serviceMap[$alias]);
    }
}
